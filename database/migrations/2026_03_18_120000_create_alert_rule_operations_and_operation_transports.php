<?php

/**
 * Zabbix-style alert rule operations: escalation steps, start-in, step duration, per-operation transports.
 *
 * @see https://www.zabbix.com/documentation/current/en/manual/config/notifications/action/operation
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
                /** @see AlertRuleOperationPhase */
                $table->string('operation_phase', 16)->default('problem');
                /** Escalation step range (Zabbix Steps From / To) */
                $table->unsignedInteger('escalation_step_from')->default(1);
                $table->unsignedInteger('escalation_step_to')->nullable();
                /** Zabbix “Start in” — delay before this operation segment (seconds) */
                $table->unsignedInteger('start_in_seconds')->default(0);
                /** Zabbix “Step duration” — repeat interval for this segment; 0 = use rule default */
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
                // Short name: MySQL max identifier length is 64; auto name would exceed it.
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
            // If a rule had no explicit mapping, seed from default transports so operations always notify somewhere.
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

    public function down(): void
    {
        if (! Schema::hasTable('alert_transport_map')) {
            Schema::create('alert_transport_map', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('rule_id');
                $table->unsignedInteger('transport_or_group_id');
                $table->string('target_type', 16);
            });
        }

        // Restore delay / interval / count / mute into alert_rules.extra (inverse of up()) before dropping operations.
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
                // up() stored legacy interval on the rule default column with step_duration_seconds = 0
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
};
