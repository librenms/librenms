<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasIndex('devices', 'devices_last_poll_attempted_index')) {
                $table->dropIndex('devices_last_poll_attempted_index');
            }

            $columnsToDrop = array_filter([
                'port',
                'transport',
                'timeout',
                'retries',
                'snmp_disable',
                'port_association_mode',
                'last_ping',
                'last_ping_timetaken',
                'last_poll_attempted',
                'agent_uptime',
            ], fn (string $col) => Schema::hasColumn('devices', $col));

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (! Schema::hasColumn('devices', 'port')) {
                $table->smallInteger('port')->unsigned()->default(161);
            }
            if (! Schema::hasColumn('devices', 'transport')) {
                $table->string('transport', 16)->default('udp');
            }
            if (! Schema::hasColumn('devices', 'timeout')) {
                $table->integer('timeout')->nullable();
            }
            if (! Schema::hasColumn('devices', 'retries')) {
                $table->integer('retries')->nullable();
            }
            if (! Schema::hasColumn('devices', 'snmp_disable')) {
                $table->boolean('snmp_disable')->default(0);
            }
            if (! Schema::hasColumn('devices', 'port_association_mode')) {
                $table->integer('port_association_mode')->default(1);
            }
            if (! Schema::hasColumn('devices', 'last_ping')) {
                $table->timestamp('last_ping')->nullable();
            }
            if (! Schema::hasColumn('devices', 'last_ping_timetaken')) {
                $table->float('last_ping_timetaken')->unsigned()->nullable();
            }
            if (! Schema::hasColumn('devices', 'last_poll_attempted')) {
                $table->timestamp('last_poll_attempted')->nullable()->index('devices_last_poll_attempted_index');
            }
            if (! Schema::hasColumn('devices', 'agent_uptime')) {
                $table->unsignedInteger('agent_uptime')->default(0);
            }
        });

        // Repopulate legacy device fields from device_polling_methods where method_type = 'snmp'
        $modeIds = ['ifIndex' => 1, 'ifName' => 2, 'ifDescr' => 3, 'ifAlias' => 4];

        DB::table('device_polling_methods')
            ->where('method_type', 'snmp')
            ->chunkById(100, function ($pollingMethods) use ($modeIds) {
                foreach ($pollingMethods as $deviceMethod) {
                    $settings = json_decode($deviceMethod->settings, true) ?: [];
                    $modeId = $modeIds[$settings['port_association_mode'] ?? 'ifIndex'] ?? 1;

                    DB::table('devices')
                        ->where('device_id', $deviceMethod->device_id)
                        ->update([
                            'port' => $settings['port'] ?? 161,
                            'transport' => $settings['transport'] ?? 'udp',
                            'timeout' => $settings['timeout'] ?? null,
                            'retries' => $settings['retries'] ?? null,
                            'snmp_disable' => ! $deviceMethod->enabled,
                            'port_association_mode' => $modeId,
                        ]);
                }
            });
    }
};
