<?php

/**
 * Adds the alert_faults table plus supporting columns on alert_log (fault_id),
 * alert_rules (notify_per_entity) and alerts (open_fault_count), then backfills one
 * open fault per currently active alert.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LibreNMS\Enum\AlertState;

return new class extends Migration
{
    public function up(): void
    {
        $this->createFaultsTable();
        $this->addSupportingColumns();
        $this->backfillFaults();
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_faults');

        if (Schema::hasColumn('alert_log', 'fault_id')) {
            Schema::table('alert_log', function (Blueprint $table) {
                $table->dropIndex('alert_log_fault_id_index');
                $table->dropColumn('fault_id');
            });
        }

        if (Schema::hasColumn('alert_rules', 'notify_per_entity')) {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->dropColumn('notify_per_entity');
            });
        }

        if (Schema::hasColumn('alerts', 'open_fault_count')) {
            Schema::table('alerts', function (Blueprint $table) {
                $table->dropColumn('open_fault_count');
            });
        }
    }

    private function createFaultsTable(): void
    {
        if (Schema::hasTable('alert_faults')) {
            return;
        }

        Schema::create('alert_faults', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rule_id');
            $table->unsignedInteger('device_id');
            $table->string('entity_type', 64)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_key', 255)->default('');
            $table->integer('state')->default(AlertState::ACTIVE);
            $table->integer('alerted')->default(0);
            $table->integer('open')->default(1);
            $table->string('severity', 16)->nullable();
            $table->text('note')->nullable();
            $table->text('info')->nullable();
            $table->binary('details')->nullable();
            $table->timestamp('first_seen')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('timestamp')->useCurrent();

            $table->index(['rule_id', 'device_id', 'entity_key']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['device_id', 'open']);
            $table->index('state');
        });

        if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
            DB::statement('ALTER TABLE `alert_faults` CHANGE `details` `details` longblob NULL ;');
        }
    }

    private function addSupportingColumns(): void
    {
        if (! Schema::hasColumn('alert_log', 'fault_id')) {
            Schema::table('alert_log', function (Blueprint $table) {
                $table->unsignedInteger('fault_id')->nullable()->after('device_id');
                $table->index('fault_id');
            });
        }

        if (! Schema::hasColumn('alert_rules', 'notify_per_entity')) {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->boolean('notify_per_entity')->default(false)->after('invert_map');
            });
        }

        if (! Schema::hasColumn('alerts', 'open_fault_count')) {
            Schema::table('alerts', function (Blueprint $table) {
                $table->unsignedInteger('open_fault_count')->default(0)->after('open');
            });
        }
    }

    private function backfillFaults(): void
    {
        if (! Schema::hasTable('alerts')) {
            return;
        }

        // Active states that should carry an open fault after the upgrade.
        $activeStates = [AlertState::ACTIVE, AlertState::ACKNOWLEDGED, AlertState::WORSE, AlertState::BETTER, AlertState::CHANGED];

        foreach (DB::table('alerts')->whereIn('state', $activeStates)->orderBy('id')->get() as $alert) {
            // Faults only carry ACTIVE/ACKNOWLEDGED/RECOVERED; collapse worse/better/changed to active.
            $faultState = (int) $alert->state === AlertState::ACKNOWLEDGED ? AlertState::ACKNOWLEDGED : AlertState::ACTIVE;

            $latestLog = DB::table('alert_log')
                ->where('rule_id', $alert->rule_id)
                ->where('device_id', $alert->device_id)
                ->orderByDesc('id')
                ->first(['id', 'details', 'time_logged']);

            $faultId = DB::table('alert_faults')->insertGetId([
                'rule_id' => $alert->rule_id,
                'device_id' => $alert->device_id,
                'entity_type' => null,
                'entity_id' => null,
                'entity_key' => '',
                'state' => $faultState,
                'alerted' => (int) ($alert->alerted ?? 0),
                'open' => 1,
                'severity' => null,
                'note' => $alert->note ?? null,
                'info' => $alert->info ?? null,
                'details' => $latestLog->details ?? null,
                'first_seen' => $latestLog->time_logged ?? $alert->timestamp,
                'last_seen' => $alert->timestamp,
                'timestamp' => $alert->timestamp,
            ]);

            if ($latestLog !== null) {
                DB::table('alert_log')->where('id', $latestLog->id)->update(['fault_id' => $faultId]);
            }

            DB::table('alerts')->where('id', $alert->id)->update(['open_fault_count' => 1]);
        }
    }
};
