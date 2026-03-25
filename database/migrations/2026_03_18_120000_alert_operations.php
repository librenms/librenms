<?php

/**
 * Alert operations: per-rule operations → global named operations → segments; config key renames;
 * default step duration on alert_operations.
 *
 * Squashes former migrations:
 * - create_alert_rule_operations_and_operation_transports
 * - rename_alert_rule_operation_default_config_keys
 * - global_alert_operations
 * - alert_operation_segments
 * - move_default_operation_step_duration_to_alert_operations
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
        $this->createAlertRuleOperationsAndMigrateFromTransportMapUp();
        $this->globalAlertOperationsUp();
        $this->alertOperationSegmentsUp();
        $this->moveDefaultOperationStepDurationToAlertOperationsUp();
    }

    public function down(): void
    {
        $this->moveDefaultOperationStepDurationToAlertOperationsDown();
        $this->alertOperationSegmentsDown();
        $this->globalAlertOperationsDown();
        $this->createAlertRuleOperationsAndMigrateFromTransportMapDown();
        $this->renameAlertRuleConfigKeysDown();
    }

    // ---- rename_alert_rule_operation_default_config_keys ----

    private function renameAlertRuleConfigKeysUp(): void
    {
        if (! Schema::hasTable('config')) {
            return;
        }

        $this->renameConfigKey('alert_rule.max_alerts', 'alert_rule.default_operation_steps_to');
        $this->renameConfigKey('alert_rule.delay', 'alert_rule.default_operation_start_in');
        $this->renameConfigKey('alert_rule.interval', 'alert_rule.default_operation_step_duration');
        $this->renameConfigKey('alert_rule.mute_alerts', 'alert_rule.default_operation_notifications_suppressed');
    }

    private function renameAlertRuleConfigKeysDown(): void
    {
        if (! Schema::hasTable('config')) {
            return;
        }

        $this->renameConfigKey('alert_rule.default_operation_steps_to', 'alert_rule.max_alerts');
        $this->renameConfigKey('alert_rule.default_operation_start_in', 'alert_rule.delay');
        $this->renameConfigKey('alert_rule.default_operation_step_duration', 'alert_rule.interval');
        $this->renameConfigKey('alert_rule.default_operation_notifications_suppressed', 'alert_rule.mute_alerts');
    }

    private function renameConfigKey(string $from, string $to): void
    {
        $oldRow = DB::table('config')->where('config_name', $from)->first();
        if (! $oldRow) {
            return;
        }

        $newExists = DB::table('config')->where('config_name', $to)->exists();
        if ($newExists) {
            DB::table('config')->where('config_name', $from)->delete();

            return;
        }

        DB::table('config')
            ->where('config_name', $from)
            ->update(['config_name' => $to]);
    }

    // ---- create_alert_rule_operations_and_operation_transports ----

    private function createAlertRuleOperationsAndMigrateFromTransportMapUp(): void
    {
        if (! Schema::hasColumn('alert_rules', 'default_operation_step_duration_seconds')) {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->unsignedInteger('default_operation_step_duration_seconds')->nullable()->after('notes');
            });
        }

        if (! Schema::hasTable('alert_rule_operations')) {
            Schema::create('alert_rule_operations', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('rule_id');
                $table->unsignedSmallInteger('position')->default(0);
                $table->string('operation_phase', 16)->default('problem');
                $table->unsignedInteger('escalation_step_from')->default(1);
                $table->unsignedInteger('escalation_step_to')->nullable();
                $table->unsignedInteger('start_in_seconds')->default(0);
                $table->unsignedInteger('step_duration_seconds')->default(0);
                $table->boolean('notifications_suppressed')->default(false);
                $table->foreign('rule_id')->references('id')->on('alert_rules')->cascadeOnDelete();
                $table->index(['rule_id', 'position']);
                $table->index(['rule_id', 'operation_phase']);
            });
        }

        if (! Schema::hasTable('alert_rule_operation_transport_map')) {
            Schema::create('alert_rule_operation_transport_map', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('operation_id');
                $table->unsignedInteger('transport_or_group_id');
                $table->string('target_type', 16);
                $table->foreign('operation_id', 'aro_tm_operation_fk')
                    ->references('id')->on('alert_rule_operations')->cascadeOnDelete();
                $table->index(['operation_id', 'target_type'], 'aro_tm_op_target_idx');
            });
        }

        $defaultTransportIds = DB::table('alert_transports')
            ->where('is_default', true)
            ->pluck('transport_id')
            ->all();

        foreach (DB::table('alert_rules')->orderBy('id')->get() as $rule) {
            if (DB::table('alert_rule_operations')->where('rule_id', $rule->id)->exists()) {
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

            DB::table('alert_rules')->where('id', $rule->id)->update([
                'default_operation_step_duration_seconds' => max(0, $interval),
            ]);

            $opId = DB::table('alert_rule_operations')->insertGetId([
                'rule_id' => $rule->id,
                'position' => 0,
                'operation_phase' => 'problem',
                'escalation_step_from' => $from,
                'escalation_step_to' => $to,
                'start_in_seconds' => max(0, $delay),
                'step_duration_seconds' => 0,
                'notifications_suppressed' => $mute,
            ]);

            $maps = DB::table('alert_transport_map')->where('rule_id', $rule->id)->get();
            foreach ($maps as $m) {
                DB::table('alert_rule_operation_transport_map')->insert([
                    'operation_id' => $opId,
                    'transport_or_group_id' => $m->transport_or_group_id,
                    'target_type' => $m->target_type,
                ]);
            }
            if ($maps->isEmpty()) {
                foreach ($defaultTransportIds as $transportId) {
                    DB::table('alert_rule_operation_transport_map')->insert([
                        'operation_id' => $opId,
                        'transport_or_group_id' => $transportId,
                        'target_type' => 'single',
                    ]);
                }
            }

            foreach (['count', 'delay', 'interval', 'mute'] as $k) {
                unset($extra[$k]);
            }
            DB::table('alert_rules')->where('id', $rule->id)->update([
                'extra' => json_encode($extra),
            ]);
        }

        Schema::dropIfExists('alert_transport_map');
    }

    private function createAlertRuleOperationsAndMigrateFromTransportMapDown(): void
    {
        if (! Schema::hasTable('alert_transport_map')) {
            Schema::create('alert_transport_map', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('rule_id');
                $table->unsignedInteger('transport_or_group_id');
                $table->string('target_type', 16);
            });
        }

        if (Schema::hasTable('alert_rule_operations')) {
            foreach (DB::table('alert_rules')->orderBy('id')->get() as $rule) {
                $op = DB::table('alert_rule_operations')
                    ->where('rule_id', $rule->id)
                    ->where('operation_phase', 'problem')
                    ->orderBy('position')
                    ->orderBy('id')
                    ->first();

                if ($op === null) {
                    continue;
                }

                $extra = [];
                if (! empty($rule->extra)) {
                    $decoded = json_decode((string) $rule->extra, true);
                    $extra = is_array($decoded) ? $decoded : [];
                }

                $extra['delay'] = (int) $op->start_in_seconds;
                $stepDur = (int) $op->step_duration_seconds;
                $defaultStep = (int) ($rule->default_operation_step_duration_seconds ?? 0);
                $extra['interval'] = $stepDur > 0 ? $stepDur : $defaultStep;

                if ($op->escalation_step_to === null) {
                    $extra['count'] = -1;
                } else {
                    $from = (int) $op->escalation_step_from;
                    $to = (int) $op->escalation_step_to;
                    $extra['count'] = $to - $from + 1;
                }

                $extra['mute'] = (bool) $op->notifications_suppressed;

                DB::table('alert_rules')->where('id', $rule->id)->update([
                    'extra' => json_encode($extra),
                ]);
            }

            foreach (DB::table('alert_rule_operations')->orderBy('rule_id')->orderBy('position')->get() as $op) {
                $maps = DB::table('alert_rule_operation_transport_map')->where('operation_id', $op->id)->get();
                foreach ($maps as $m) {
                    DB::table('alert_transport_map')->insert([
                        'rule_id' => $op->rule_id,
                        'transport_or_group_id' => $m->transport_or_group_id,
                        'target_type' => $m->target_type,
                    ]);
                }
            }
        }

        Schema::dropIfExists('alert_rule_operation_transport_map');
        Schema::dropIfExists('alert_rule_operations');

        if (Schema::hasColumn('alert_rules', 'default_operation_step_duration_seconds')) {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->dropColumn('default_operation_step_duration_seconds');
            });
        }
    }

    // ---- global_alert_operations ----

    private function globalAlertOperationsUp(): void
    {
        if (! Schema::hasTable('alert_operations')) {
            Schema::create('alert_operations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->unsignedSmallInteger('position')->default(0);
                $table->string('operation_phase', 16)->default('problem');
                $table->unsignedInteger('escalation_step_from')->default(1);
                $table->unsignedInteger('escalation_step_to')->nullable();
                $table->unsignedInteger('start_in_seconds')->default(0);
                $table->unsignedInteger('step_duration_seconds')->default(0);
                $table->boolean('notifications_suppressed')->default(false);
                $table->index(['operation_phase']);
            });
        }

        if (! Schema::hasTable('alert_operation_transport_map')) {
            Schema::create('alert_operation_transport_map', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('alert_operation_id');
                $table->unsignedInteger('transport_or_group_id');
                $table->string('target_type', 16);
                $table->foreign('alert_operation_id', 'ao_tm_ao_fk')
                    ->references('id')->on('alert_operations')->cascadeOnDelete();
                $table->index(['alert_operation_id', 'target_type'], 'ao_tm_ao_target_idx');
            });
        }

        if (! Schema::hasColumn('alert_rules', 'alert_operation_id')) {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->unsignedInteger('alert_operation_id')->nullable()->after('default_operation_step_duration_seconds');
                $table->foreign('alert_operation_id', 'alert_rules_alert_operation_fk')
                    ->references('id')->on('alert_operations')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('alert_rule_operations')) {
            return;
        }

        foreach (DB::table('alert_rules')->orderBy('id')->get() as $rule) {
            $oldOps = DB::table('alert_rule_operations')
                ->where('rule_id', $rule->id)
                ->orderBy('position')
                ->orderBy('id')
                ->get();
            if ($oldOps->isEmpty()) {
                continue;
            }

            $first = $oldOps->first();
            $baseName = trim((string) $rule->name) !== '' ? (string) $rule->name : 'Rule #' . $rule->id;
            $name = mb_substr($baseName . ' — operation', 0, 255);

            $newId = DB::table('alert_operations')->insertGetId([
                'name' => $name,
                'position' => 0,
                'operation_phase' => $first->operation_phase,
                'escalation_step_from' => $first->escalation_step_from,
                'escalation_step_to' => $first->escalation_step_to,
                'start_in_seconds' => $first->start_in_seconds,
                'step_duration_seconds' => $first->step_duration_seconds,
                'notifications_suppressed' => $first->notifications_suppressed,
            ]);

            $seen = [];
            foreach ($oldOps as $oldOp) {
                $maps = DB::table('alert_rule_operation_transport_map')->where('operation_id', $oldOp->id)->get();
                foreach ($maps as $m) {
                    $key = $m->target_type . ':' . $m->transport_or_group_id;
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    DB::table('alert_operation_transport_map')->insert([
                        'alert_operation_id' => $newId,
                        'transport_or_group_id' => $m->transport_or_group_id,
                        'target_type' => $m->target_type,
                    ]);
                }
            }

            DB::table('alert_rules')->where('id', $rule->id)->update(['alert_operation_id' => $newId]);
        }

        Schema::dropIfExists('alert_rule_operation_transport_map');
        Schema::dropIfExists('alert_rule_operations');
    }

    private function globalAlertOperationsDown(): void
    {
        if (! Schema::hasTable('alert_rule_operations')) {
            Schema::create('alert_rule_operations', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('rule_id');
                $table->unsignedSmallInteger('position')->default(0);
                $table->string('operation_phase', 16)->default('problem');
                $table->unsignedInteger('escalation_step_from')->default(1);
                $table->unsignedInteger('escalation_step_to')->nullable();
                $table->unsignedInteger('start_in_seconds')->default(0);
                $table->unsignedInteger('step_duration_seconds')->default(0);
                $table->boolean('notifications_suppressed')->default(false);
                $table->foreign('rule_id')->references('id')->on('alert_rules')->cascadeOnDelete();
                $table->index(['rule_id', 'position']);
                $table->index(['rule_id', 'operation_phase']);
            });
        }

        if (! Schema::hasTable('alert_rule_operation_transport_map')) {
            Schema::create('alert_rule_operation_transport_map', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('operation_id');
                $table->unsignedInteger('transport_or_group_id');
                $table->string('target_type', 16);
                $table->foreign('operation_id', 'aro_tm_operation_fk')
                    ->references('id')->on('alert_rule_operations')->cascadeOnDelete();
                $table->index(['operation_id', 'target_type'], 'aro_tm_op_target_idx');
            });
        }

        if (Schema::hasColumn('alert_rules', 'alert_operation_id')) {
            foreach (DB::table('alert_rules')->whereNotNull('alert_operation_id')->orderBy('id')->get() as $rule) {
                $op = DB::table('alert_operations')->where('id', $rule->alert_operation_id)->first();
                if ($op === null) {
                    continue;
                }

                $opId = DB::table('alert_rule_operations')->insertGetId([
                    'rule_id' => $rule->id,
                    'position' => $op->position,
                    'operation_phase' => $op->operation_phase,
                    'escalation_step_from' => $op->escalation_step_from,
                    'escalation_step_to' => $op->escalation_step_to,
                    'start_in_seconds' => $op->start_in_seconds,
                    'step_duration_seconds' => $op->step_duration_seconds,
                    'notifications_suppressed' => $op->notifications_suppressed,
                ]);

                foreach (DB::table('alert_operation_transport_map')->where('alert_operation_id', $op->id)->get() as $m) {
                    DB::table('alert_rule_operation_transport_map')->insert([
                        'operation_id' => $opId,
                        'transport_or_group_id' => $m->transport_or_group_id,
                        'target_type' => $m->target_type,
                    ]);
                }
            }

            Schema::table('alert_rules', function (Blueprint $table) {
                $table->dropForeign('alert_rules_alert_operation_fk');
                $table->dropColumn('alert_operation_id');
            });
        }

        Schema::dropIfExists('alert_operation_transport_map');
        Schema::dropIfExists('alert_operations');
    }

    // ---- alert_operation_segments ----

    private function alertOperationSegmentsUp(): void
    {
        if (Schema::hasTable('alert_operation_segments')) {
            return;
        }

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
            $table->foreign('alert_operation_id', 'aos_ao_fk')
                ->references('id')->on('alert_operations')->cascadeOnDelete();
            $table->index(['alert_operation_id', 'position'], 'aos_ao_position_idx');
            $table->index(['alert_operation_id', 'operation_phase'], 'aos_ao_phase_idx');
        });

        if (! Schema::hasColumn('alert_operation_transport_map', 'segment_id')) {
            Schema::table('alert_operation_transport_map', function (Blueprint $table) {
                $table->unsignedInteger('segment_id')->nullable()->after('id');
            });
        }

        foreach (DB::table('alert_operations')->orderBy('id')->get() as $op) {
            $segId = DB::table('alert_operation_segments')->insertGetId([
                'alert_operation_id' => $op->id,
                'position' => (int) ($op->position ?? 0),
                'operation_phase' => $op->operation_phase,
                'escalation_step_from' => (int) $op->escalation_step_from,
                'escalation_step_to' => $op->escalation_step_to,
                'start_in_seconds' => (int) $op->start_in_seconds,
                'step_duration_seconds' => (int) $op->step_duration_seconds,
                'notifications_suppressed' => (bool) $op->notifications_suppressed,
            ]);

            DB::table('alert_operation_transport_map')
                ->where('alert_operation_id', $op->id)
                ->update(['segment_id' => $segId]);
        }

        Schema::table('alert_operation_transport_map', function (Blueprint $table) {
            $table->dropForeign('ao_tm_ao_fk');
        });

        Schema::table('alert_operation_transport_map', function (Blueprint $table) {
            $table->dropColumn('alert_operation_id');
        });

        DB::statement('ALTER TABLE `alert_operation_transport_map` MODIFY `segment_id` INT UNSIGNED NOT NULL');

        Schema::table('alert_operation_transport_map', function (Blueprint $table) {
            $table->foreign('segment_id', 'ao_tm_seg_fk')
                ->references('id')->on('alert_operation_segments')->cascadeOnDelete();
            $table->index(['segment_id', 'target_type'], 'ao_tm_seg_target_idx');
        });

        Schema::table('alert_operations', function (Blueprint $table) {
            foreach ([
                'position',
                'operation_phase',
                'escalation_step_from',
                'escalation_step_to',
                'start_in_seconds',
                'step_duration_seconds',
                'notifications_suppressed',
            ] as $col) {
                if (Schema::hasColumn('alert_operations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function alertOperationSegmentsDown(): void
    {
        if (! Schema::hasTable('alert_operation_segments')) {
            return;
        }

        Schema::table('alert_operations', function (Blueprint $table) {
            if (! Schema::hasColumn('alert_operations', 'position')) {
                $table->unsignedSmallInteger('position')->default(0)->after('name');
                $table->string('operation_phase', 16)->default('problem');
                $table->unsignedInteger('escalation_step_from')->default(1);
                $table->unsignedInteger('escalation_step_to')->nullable();
                $table->unsignedInteger('start_in_seconds')->default(0);
                $table->unsignedInteger('step_duration_seconds')->default(0);
                $table->boolean('notifications_suppressed')->default(false);
            }
        });

        foreach (DB::table('alert_operations')->orderBy('id')->get() as $op) {
            $seg = DB::table('alert_operation_segments')
                ->where('alert_operation_id', $op->id)
                ->orderBy('position')
                ->orderBy('id')
                ->first();
            if ($seg === null) {
                continue;
            }
            DB::table('alert_operations')->where('id', $op->id)->update([
                'position' => $seg->position,
                'operation_phase' => $seg->operation_phase,
                'escalation_step_from' => $seg->escalation_step_from,
                'escalation_step_to' => $seg->escalation_step_to,
                'start_in_seconds' => $seg->start_in_seconds,
                'step_duration_seconds' => $seg->step_duration_seconds,
                'notifications_suppressed' => $seg->notifications_suppressed,
            ]);
        }

        $this->dropAlertOperationTransportMapSegmentForeignKey();

        try {
            DB::statement('ALTER TABLE `alert_operation_transport_map` DROP INDEX `ao_tm_seg_target_idx`');
        } catch (\Throwable) {
        }

        Schema::table('alert_operation_transport_map', function (Blueprint $table) {
            $table->unsignedInteger('alert_operation_id')->nullable()->after('id');
        });

        foreach (DB::table('alert_operation_segments')->orderBy('id')->get() as $seg) {
            DB::table('alert_operation_transport_map')
                ->where('segment_id', $seg->id)
                ->update(['alert_operation_id' => $seg->alert_operation_id]);
        }

        Schema::table('alert_operation_transport_map', function (Blueprint $table) {
            $table->dropColumn('segment_id');
        });

        DB::statement('ALTER TABLE `alert_operation_transport_map` MODIFY `alert_operation_id` INT UNSIGNED NOT NULL');

        try {
            Schema::table('alert_operation_transport_map', function (Blueprint $table) {
                $table->foreign('alert_operation_id', 'ao_tm_ao_fk')
                    ->references('id')->on('alert_operations')->cascadeOnDelete();
            });
        } catch (\Throwable) {
        }

        try {
            Schema::table('alert_operation_transport_map', function (Blueprint $table) {
                $table->index(['alert_operation_id', 'target_type'], 'ao_tm_ao_target_idx');
            });
        } catch (\Throwable) {
        }

        Schema::dropIfExists('alert_operation_segments');
    }

    /**
     * MySQL may use an auto-generated FK name; partial rollbacks can leave the table without ao_tm_seg_fk.
     */
    private function dropAlertOperationTransportMapSegmentForeignKey(): void
    {
        if (! Schema::hasTable('alert_operation_transport_map') || ! Schema::hasColumn('alert_operation_transport_map', 'segment_id')) {
            return;
        }

        try {
            Schema::table('alert_operation_transport_map', function (Blueprint $table) {
                $table->dropForeign(['segment_id']);
            });

            return;
        } catch (\Throwable) {
        }

        $connection = Schema::getConnection();
        if (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            $db = $connection->getDatabaseName();
            $names = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', $db)
                ->where('TABLE_NAME', 'alert_operation_transport_map')
                ->where('COLUMN_NAME', 'segment_id')
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->distinct()
                ->pluck('CONSTRAINT_NAME');

            foreach ($names as $name) {
                try {
                    DB::statement('ALTER TABLE `alert_operation_transport_map` DROP FOREIGN KEY `' . str_replace('`', '``', (string) $name) . '`');
                } catch (\Throwable) {
                }
            }

            return;
        }

        foreach (['ao_tm_seg_fk'] as $constraint) {
            try {
                Schema::table('alert_operation_transport_map', function (Blueprint $table) use ($constraint) {
                    $table->dropForeign($constraint);
                });
            } catch (\Throwable) {
            }
        }
    }

    // ---- move_default_operation_step_duration_to_alert_operations ----

    private function moveDefaultOperationStepDurationToAlertOperationsUp(): void
    {
        if (! Schema::hasTable('alert_operations')) {
            return;
        }

        if (! Schema::hasColumn('alert_operations', 'default_operation_step_duration_seconds')) {
            Schema::table('alert_operations', function (Blueprint $table): void {
                $table->unsignedInteger('default_operation_step_duration_seconds')->nullable()->after('name');
            });
        }

        if (Schema::hasColumn('alert_rules', 'default_operation_step_duration_seconds')) {
            $opIds = DB::table('alert_rules')
                ->whereNotNull('alert_operation_id')
                ->distinct()
                ->pluck('alert_operation_id');

            foreach ($opIds as $opId) {
                $max = DB::table('alert_rules')
                    ->where('alert_operation_id', $opId)
                    ->whereNotNull('default_operation_step_duration_seconds')
                    ->max('default_operation_step_duration_seconds');

                if ($max !== null) {
                    DB::table('alert_operations')->where('id', $opId)->update([
                        'default_operation_step_duration_seconds' => (int) $max,
                    ]);
                }
            }

            Schema::table('alert_rules', function (Blueprint $table): void {
                $table->dropColumn('default_operation_step_duration_seconds');
            });
        }
    }

    private function moveDefaultOperationStepDurationToAlertOperationsDown(): void
    {
        if (! Schema::hasTable('alert_rules')) {
            return;
        }

        if (! Schema::hasColumn('alert_rules', 'default_operation_step_duration_seconds')) {
            Schema::table('alert_rules', function (Blueprint $table): void {
                $table->unsignedInteger('default_operation_step_duration_seconds')->nullable()->after('notes');
            });
        }

        if (Schema::hasColumn('alert_operations', 'default_operation_step_duration_seconds')) {
            $rules = DB::table('alert_rules')->whereNotNull('alert_operation_id')->get(['id', 'alert_operation_id']);
            foreach ($rules as $rule) {
                $sec = DB::table('alert_operations')
                    ->where('id', $rule->alert_operation_id)
                    ->value('default_operation_step_duration_seconds');
                if ($sec !== null) {
                    DB::table('alert_rules')->where('id', $rule->id)->update([
                        'default_operation_step_duration_seconds' => (int) $sec,
                    ]);
                }
            }

            Schema::table('alert_operations', function (Blueprint $table): void {
                $table->dropColumn('default_operation_step_duration_seconds');
            });
        }
    }
};
