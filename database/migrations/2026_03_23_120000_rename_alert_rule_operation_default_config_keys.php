<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('config')) {
            return;
        }

        $this->renameConfigKey('alert_rule.max_alerts', 'alert_rule.default_operation_steps_to');
        $this->renameConfigKey('alert_rule.delay', 'alert_rule.default_operation_start_in');
        $this->renameConfigKey('alert_rule.interval', 'alert_rule.default_operation_step_duration');
        $this->renameConfigKey('alert_rule.mute_alerts', 'alert_rule.default_operation_notifications_suppressed');
    }

    public function down(): void
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
            // Prefer explicitly set new key value if present.
            DB::table('config')->where('config_name', $from)->delete();

            return;
        }

        DB::table('config')
            ->where('config_name', $from)
            ->update(['config_name' => $to]);
    }
};
