<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_FIELD = 'devices.last_ping_timetaken';
    private const NEW_FIELD = 'device_stats.ping_rtt_last';
    private const NEW_SQL = '(SELECT ping_rtt_last FROM device_stats WHERE device_stats.device_id = devices.device_id)';

    /**
     * Ping data is stored in device_stats, agent_uptime and last_poll_attempted are never written.
     */
    public function up(): void
    {
        $this->rewriteAlertRules(self::OLD_FIELD, self::NEW_FIELD, self::OLD_FIELD, self::NEW_SQL);

        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasIndex('devices', 'devices_last_poll_attempted_index')) {
                $table->dropIndex('devices_last_poll_attempted_index');
            }

            $table->dropColumn(array_filter(
                ['last_ping', 'last_ping_timetaken', 'last_poll_attempted', 'agent_uptime'],
                fn (string $column) => Schema::hasColumn('devices', $column),
            ));
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->unsignedInteger('agent_uptime')->default(0)->after('uptime');
            $table->timestamp('last_poll_attempted')->nullable()->index('devices_last_poll_attempted_index')->after('last_polled');
            $table->timestamp('last_ping')->nullable()->after('last_discovered');
            $table->double('last_ping_timetaken')->unsigned()->nullable()->after('last_ping');
        });

        $this->rewriteAlertRules(self::NEW_FIELD, self::OLD_FIELD, self::NEW_SQL, self::OLD_FIELD);
    }

    /**
     * Rules may filter on the ping latency, point them at the new location.
     */
    private function rewriteAlertRules(string $fromField, string $toField, string $fromSql, string $toSql): void
    {
        DB::table('alert_rules')
            ->where('builder', 'like', "%$fromField%")
            ->orWhere('query', 'like', "%$fromSql%")
            ->get(['id', 'builder', 'query'])
            ->each(fn ($rule) => DB::table('alert_rules')->where('id', $rule->id)->update([
                'builder' => str_replace("\"$fromField\"", "\"$toField\"", (string) $rule->builder),
                'query' => str_replace($fromSql, $toSql, (string) $rule->query),
            ]));
    }
};
