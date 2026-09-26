<?php

use App\Facades\LibrenmsConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasAgentUptime = Schema::hasColumn('devices', 'agent_uptime');
        /** @var array<string, array{group?: string}> $allOs */
        $allOs = (array) LibrenmsConfig::get('os', []);
        $unixOses = array_keys(array_filter($allOs, fn (array $cfg): bool => ($cfg['group'] ?? null) === 'unix'));

        // Small chunks, each in a short transaction, to avoid holding locks for long
        DB::table('devices')
            ->select($hasAgentUptime ? ['device_id', 'agent_uptime'] : ['device_id'])
            ->where(function ($q) use ($hasAgentUptime, $unixOses) {
                if (! empty($unixOses)) {
                    $q->whereIn('os', $unixOses);
                }
                $q->orWhere('os', '=', 'windows');

                if ($hasAgentUptime) {
                    $q->orWhere('agent_uptime', '>', 0);
                }
            })
            ->chunkById(100, function ($devices) {
                DB::transaction(function () use ($devices) {
                    // Skip devices that already have a unix agent polling method
                    $existingDeviceIds = DB::table('device_polling_methods')
                        ->where('method_type', 'unix-agent')
                        ->whereIn('device_id', $devices->pluck('device_id'))
                        ->pluck('device_id')
                        ->all();

                    $pollingMethods = [];
                    foreach ($devices as $device) {
                        if (in_array($device->device_id, $existingDeviceIds)) {
                            continue;
                        }

                        $pollingMethods[] = [
                            'device_id' => $device->device_id,
                            'method_type' => 'unix-agent',
                            'enabled' => true,
                            'affects_availability' => false,
                            'last_check_successful' => ($device->agent_uptime ?? 0) > 0 ? true : null,
                            'secret_id' => null,
                            'settings' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (! empty($pollingMethods)) {
                        DB::table('device_polling_methods')->insert($pollingMethods);
                    }
                });
            }, 'device_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('device_polling_methods')
            ->where('method_type', 'unix-agent')
            ->delete();
    }
};
