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
        $globalIcmpCheck = (bool) \App\Facades\LibrenmsConfig::get('icmp_check', true);

        // Small chunks, each in a short transaction, to avoid holding locks for long
        DB::table('devices')
            ->select(['device_id', 'status', 'status_reason'])
            ->chunkById(100, function ($devices) use ($globalIcmpCheck) {
                DB::transaction(function () use ($devices, $globalIcmpCheck) {
                    $deviceIds = $devices->pluck('device_id')->all();

                    // Skip devices that already have an ICMP polling method
                    $existingDeviceIds = DB::table('device_polling_methods')
                        ->where('method_type', 'icmp')
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
                        $enabled = $override === null ? $globalIcmpCheck : ! in_array($override, ['1', 'true'], true);

                        $pollingMethods[] = [
                            'device_id' => $device->device_id,
                            'method_type' => 'icmp',
                            'enabled' => $enabled,
                            'affects_availability' => true,
                            'last_check_successful' => $this->lastCheckSuccessful($device, 'icmp'),
                            'secret_id' => null,
                            'settings' => json_encode(['ip_version' => 'match_snmp_transport']),
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
     * Whether the legacy availability check for this source passed.
     * A down device lists the failed sources in status_reason, without a reason it is not known which failed.
     */
    private function lastCheckSuccessful(object $device, string $source): bool
    {
        if ($device->status) {
            return true;
        }

        $failed = array_filter(array_map('trim', explode(',', (string) $device->status_reason)));

        return ! empty($failed) && ! in_array($source, $failed, true);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('device_polling_methods')->where('method_type', 'icmp')->delete();
    }
};
