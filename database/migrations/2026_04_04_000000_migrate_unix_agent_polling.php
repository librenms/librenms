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
        DB::transaction(function () {
            $hasAgentUptime = Schema::hasColumn('devices', 'agent_uptime');
            $unixOses = collect(LibrenmsConfig::get('os', []))->filter(fn ($cfg) => ($cfg['group'] ?? null) === 'unix')->keys()->all();

            $query = DB::table('devices')
                ->where(function ($q) use ($hasAgentUptime, $unixOses) {
                    if (! empty($unixOses)) {
                        $q->whereIn('os', $unixOses);
                    }
                    $q->orWhere('os', '=', 'windows');

                    if ($hasAgentUptime) {
                        $q->orWhere('agent_uptime', '>', 0);
                    }
                })
                ->orderBy('devices.device_id');

            $query->chunk(100, function ($devices) use ($hasAgentUptime) {
                $pollingMethods = [];
                foreach ($devices as $device) {
                    $lastSuccessful = ($hasAgentUptime && isset($device->agent_uptime) && $device->agent_uptime > 0) ? true : null;

                    $pollingMethods[] = [
                        'device_id' => $device->device_id,
                        'method_type' => 'unix-agent',
                        'enabled' => true,
                        'affects_availability' => false,
                        'last_check_successful' => $lastSuccessful,
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
        });
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
