<?php

/**
 * Alert operations: migrate legacy rule transport map + rule.extra timing keys into
 * named alert operations + segments.
 *
 * This migration intentionally keeps indexes simple and does not create foreign keys
 * to remain portable across database drivers (notably SQLite in CI).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->renameAlertRuleConfigKeysUp();
        $this->createOperationsSchemaUp();
        $this->migrateRulesToOperationsUp();
    }

    public function down(): void
    {
        $this->migrateOperationsToRulesDown();
        $this->dropOperationsSchemaDown();
        $this->renameAlertRuleConfigKeysDown();
    }

    // ---- rename_alert_rule_operation_default_config_keys ----

    private function renameAlertRuleConfigKeysUp(): void
    {
        if (! Schema::hasTable('config')) {
            return;
        }

        // Non-destructive: copy legacy keys to new names (do not delete/rename old keys).
        $this->copyConfigKey('alert_rule.max_alerts', 'alert_rule.default_operation_steps_to');
        $this->copyConfigKey('alert_rule.delay', 'alert_rule.default_operation_start_in');
        $this->copyConfigKey('alert_rule.interval', 'alert_rule.default_operation_step_duration');
        $this->copyConfigKey('alert_rule.mute_alerts', 'alert_rule.default_operation_notifications_suppressed');
    }

    private function renameAlertRuleConfigKeysDown(): void
    {
        if (! Schema::hasTable('config')) {
            return;
        }

        // Non-destructive: leave config rows as-is on rollback.
    }

    private function copyConfigKey(string $from, string $to): void
    {
        $value = DB::table('config')->where('config_name', $from)->value('config_value');
        if ($value === null) {
            return;
        }

        if (DB::table('config')->where('config_name', $to)->exists()) {
            return;
        }

        DB::table('config')->insert([
            'config_name' => $to,
            'config_value' => $value,
        ]);
    }

    // ---- operations schema (no FKs, simple indexes) ----

    private function createOperationsSchemaUp(): void
    {
        if (! Schema::hasTable('alert_operations')) {
            Schema::create('alert_operations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->unsignedInteger('default_operation_step_duration_seconds')->nullable();
            });
        }

        if (! Schema::hasTable('alert_operation_segments')) {
            Schema::create('alert_operation_segments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('alert_operation_id');
                $table->unsignedSmallInteger('position')->default(0);
                $table->string('operation_phase', 16)->default('problem');
                $table->unsignedInteger('escalation_step_from')->default(1);
                $table->unsignedInteger('escalation_step_to')->nullable();
                $table->unsignedInteger('start_in_seconds')->default(0);
                $table->unsignedInteger('step_duration_seconds')->default(0);
                $table->boolean('notifications_suppressed')->default(false);
                $table->index(['alert_operation_id']);
            });
        }

        if (! Schema::hasTable('alert_operation_transport_map')) {
            Schema::create('alert_operation_transport_map', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('segment_id');
                $table->unsignedInteger('transport_or_group_id');
                $table->string('target_type', 16);
                $table->index(['segment_id']);
            });
        }

        if (! Schema::hasColumn('alert_rules', 'alert_operation_id')) {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->unsignedInteger('alert_operation_id')->nullable()->after('notes');
                $table->index(['alert_operation_id']);
            });
        }
    }

    private function migrateRulesToOperationsUp(): void
    {
        if (! Schema::hasTable('alert_rules')) {
            return;
        }

        $hasLegacyTransportMap = Schema::hasTable('alert_transport_map');
        $defaultTransportIds = [];
        if (Schema::hasTable('alert_transports')) {
            $defaultTransportIds = DB::table('alert_transports')
                ->where('is_default', true)
                ->pluck('transport_id')
                ->all();
        }

        /** @var array<string, array{op_id:int, segment_id:int}> $operationCache */
        $operationCache = [];

        foreach (DB::table('alert_rules')->orderBy('id')->get() as $rule) {
            if ($rule->alert_operation_id !== null) {
                continue;
            }

            $extra = [];
            if (! empty($rule->extra)) {
                $decoded = json_decode((string) $rule->extra, true);
                $extra = is_array($decoded) ? $decoded : [];
            }

            $delay = (int) ($extra['delay'] ?? 0);
            $interval = (int) ($extra['interval'] ?? 0);
            $count = isset($extra['count']) ? (int) $extra['count'] : -1;
            $mute = filter_var($extra['mute'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $from = 1;
            if ($count === -1) {
                $to = null;
            } elseif ($count > 0) {
                $to = $from + $count - 1;
            } else {
                $to = $from;
            }

            $transportRows = [];
            if ($hasLegacyTransportMap) {
                $maps = DB::table('alert_transport_map')->where('rule_id', $rule->id)->get();
                foreach ($maps as $m) {
                    $transportRows[] = [
                        'transport_or_group_id' => (int) $m->transport_or_group_id,
                        'target_type' => (string) $m->target_type,
                    ];
                }
                if (empty($transportRows)) {
                    foreach ($defaultTransportIds as $transportId) {
                        $transportRows[] = [
                            'transport_or_group_id' => (int) $transportId,
                            'target_type' => 'single',
                        ];
                    }
                }
            }

            // Create only unique operations: key off the legacy operation parameters + transport selection.
            usort($transportRows, static function (array $a, array $b): int {
                return ($a['target_type'] <=> $b['target_type']) ?: ($a['transport_or_group_id'] <=> $b['transport_or_group_id']);
            });

            $signature = sha1((string) json_encode([
                'delay' => max(0, $delay),
                'interval' => max(0, $interval),
                'count' => $count,
                'mute' => (bool) $mute,
                'from' => $from,
                'to' => $to,
                'transports' => $transportRows,
            ]));

            if (isset($operationCache[$signature])) {
                $opId = $operationCache[$signature]['op_id'];
            } else {
                $baseName = trim((string) $rule->name) !== '' ? (string) $rule->name : 'Rule #' . $rule->id;
                $opName = mb_substr($baseName . ' — operation', 0, 255);

                $opId = DB::table('alert_operations')->insertGetId([
                    'name' => $opName,
                    'default_operation_step_duration_seconds' => max(0, $interval),
                ]);

                $segmentId = DB::table('alert_operation_segments')->insertGetId([
                    'alert_operation_id' => $opId,
                    'position' => 0,
                    'operation_phase' => 'problem',
                    'escalation_step_from' => $from,
                    'escalation_step_to' => $to,
                    'start_in_seconds' => max(0, $delay),
                    'step_duration_seconds' => 0,
                    'notifications_suppressed' => $mute,
                ]);

                foreach ($transportRows as $row) {
                    DB::table('alert_operation_transport_map')->insert([
                        'segment_id' => $segmentId,
                        'transport_or_group_id' => $row['transport_or_group_id'],
                        'target_type' => $row['target_type'],
                    ]);
                }

                $operationCache[$signature] = [
                    'op_id' => $opId,
                    'segment_id' => $segmentId,
                ];
            }

            DB::table('alert_rules')->where('id', $rule->id)->update([
                'alert_operation_id' => $opId,
            ]);
        }
    }

    private function migrateOperationsToRulesDown(): void
    {
        if (! Schema::hasTable('alert_rules') || ! Schema::hasColumn('alert_rules', 'alert_operation_id')) {
            return;
        }

        if (! Schema::hasTable('alert_transport_map')) {
            Schema::create('alert_transport_map', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('rule_id');
                $table->unsignedInteger('transport_or_group_id');
                $table->string('target_type', 16);
            });
        }

        foreach (DB::table('alert_rules')->whereNotNull('alert_operation_id')->orderBy('id')->get() as $rule) {
            $segment = null;
            if (Schema::hasTable('alert_operation_segments')) {
                $segment = DB::table('alert_operation_segments')
                    ->where('alert_operation_id', $rule->alert_operation_id)
                    ->orderBy('position')
                    ->orderBy('id')
                    ->first();
            }

            $op = null;
            if (Schema::hasTable('alert_operations')) {
                $op = DB::table('alert_operations')->where('id', $rule->alert_operation_id)->first();
            }

            $extra = [];
            if (! empty($rule->extra)) {
                $decoded = json_decode((string) $rule->extra, true);
                $extra = is_array($decoded) ? $decoded : [];
            }

            if ($segment !== null) {
                $extra['delay'] = (int) $segment->start_in_seconds;
                $stepDur = (int) $segment->step_duration_seconds;
                $defaultStep = (int) ($op->default_operation_step_duration_seconds ?? 0);
                $extra['interval'] = $stepDur > 0 ? $stepDur : $defaultStep;

                if ($segment->escalation_step_to === null) {
                    $extra['count'] = -1;
                } else {
                    $from = (int) $segment->escalation_step_from;
                    $to = (int) $segment->escalation_step_to;
                    $extra['count'] = $to - $from + 1;
                }
                $extra['mute'] = (bool) $segment->notifications_suppressed;

                if (Schema::hasTable('alert_operation_transport_map')) {
                    foreach (DB::table('alert_operation_transport_map')->where('segment_id', $segment->id)->get() as $m) {
                        DB::table('alert_transport_map')->insert([
                            'rule_id' => $rule->id,
                            'transport_or_group_id' => $m->transport_or_group_id,
                            'target_type' => $m->target_type,
                        ]);
                    }
                }
            }

            DB::table('alert_rules')->where('id', $rule->id)->update([
                'extra' => json_encode($extra),
                'alert_operation_id' => null,
            ]);
        }
    }

    private function dropOperationsSchemaDown(): void
    {
        if (Schema::hasColumn('alert_rules', 'alert_operation_id')) {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->dropColumn('alert_operation_id');
            });
        }

        Schema::dropIfExists('alert_operation_transport_map');
        Schema::dropIfExists('alert_operation_segments');
        Schema::dropIfExists('alert_operations');
    }
};
