<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $globalMtuCheck = (bool) (\App\Facades\LibrenmsConfig::get('mtu_options.bytes') ?? 0) > 0;

        // Small chunks, each in a short transaction, to avoid holding locks for long
        DB::table('devices')
            ->select(['device_id', 'mtu_status'])
            ->chunkById(100, function ($devices) use ($globalMtuCheck): void {
                DB::transaction(function () use ($devices, $globalMtuCheck): void {
                    $deviceIds = $devices->pluck('device_id')->all();

                    // Skip devices that already have an ICMP polling method
                    $existingDeviceIds = DB::table('device_polling_methods')
                        ->where('method_type', 'mtu')
                        ->whereIn('device_id', $deviceIds)
                        ->pluck('device_id')
                        ->all();

                    $overrides = DB::table('devices_attribs')
                        ->whereIn('device_id', $deviceIds)
                        ->where('attrib_type', 'override_icmp_disable')
                        ->pluck('attrib_value', 'device_id');

                    $pollingMethods = [];
                    foreach ($devices as $device) {
                        if (in_array($device->device_id, $existingDeviceIds)) {
                            continue;
                        }

                        $override = $overrides[$device->device_id] ?? null;
                        $enabled = $globalMtuCheck && ! in_array($override, ['1', 'true'], true);

                        if ($enabled) {
                            $pollingMethods[] = [
                                'device_id' => $device->device_id,
                                'method_type' => 'mtu',
                                'enabled' => $enabled,
                                'affects_availability' => true,
                                'last_check_successful' => $device->mtu_status,
                                'secret_id' => null,
                                'settings' => json_encode([]),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
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
        DB::table('device_polling_methods')->where('method_type', 'mtu')->delete();
    }
};
