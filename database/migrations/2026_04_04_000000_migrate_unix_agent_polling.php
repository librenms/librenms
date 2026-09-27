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
        $agentOses = array_keys(array_filter($allOs, fn (array $cfg): bool => ($cfg['group'] ?? null) === 'unix'));
        $agentOses[] = 'windows';

        // Small chunks, each in a short transaction, to avoid holding locks for long
        DB::table('devices')
            ->select($hasAgentUptime ? ['device_id', 'os', 'agent_uptime'] : ['device_id', 'os'])
            ->whereIn('os', $agentOses)
            ->chunkById(100, function ($devices) {
                DB::transaction(function () use ($devices) {
                    $deviceIds = $devices->pluck('device_id');

                    // Skip devices that already have a unix agent polling method
                    $existingDeviceIds = DB::table('device_polling_methods')
                        ->where('method_type', 'unix-agent')
                        ->whereIn('device_id', $deviceIds)
                        ->pluck('device_id')
                        ->all();

                    $deviceSettings = DB::table('devices_attribs')
                        ->whereIn('device_id', $deviceIds)
                        ->where('attrib_type', 'poll_unix-agent')
                        ->pluck('attrib_value', 'device_id');

                    $pollingMethods = [];
                    foreach ($devices as $device) {
                        if (in_array($device->device_id, $existingDeviceIds)) {
                            continue;
                        }

                        if (! $this->moduleEnabled($device->os, $deviceSettings[$device->device_id] ?? null)) {
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
     * Same precedence as the poller module status: device, then OS, then global.
     */
    private function moduleEnabled(string $os, ?string $deviceSetting): bool
    {
        $enabled = $deviceSetting
            ?? LibrenmsConfig::get("os.$os.poller_modules.unix-agent")
            ?? LibrenmsConfig::get('poller_modules.unix-agent', false);

        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
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
