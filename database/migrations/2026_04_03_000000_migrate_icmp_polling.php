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
        $globalIcmpCheck = \App\Facades\LibrenmsConfig::get('icmp_check', true);

        DB::transaction(function () use ($globalIcmpCheck) {
            DB::table('devices')
                ->select('devices.*', 'icmp_attrib.attrib_value as icmp_disabled_value')
                ->leftJoin('devices_attribs as icmp_attrib', function ($join) {
                    $join->on('devices.device_id', '=', 'icmp_attrib.device_id')
                        ->where('icmp_attrib.attrib_type', '=', 'override_icmp_disable');
                })
                ->orderBy('devices.device_id')->chunk(100, function ($devices) use ($globalIcmpCheck) {
                    $pollingMethods = [];
                    foreach ($devices as $device) {
                        $isDeviceOverride = $device->icmp_disabled_value !== null;
                        if ($isDeviceOverride) {
                            $enabled = ! ($device->icmp_disabled_value === '1' || $device->icmp_disabled_value === 'true');
                        } else {
                            $enabled = (bool) $globalIcmpCheck;
                        }

                        $pollingMethods[] = [
                            'device_id' => $device->device_id,
                            'method_type' => 'icmp',
                            'enabled' => $enabled,
                            'affects_availability' => true,
                            'last_check_successful' => (bool) $device->status,
                            'secret_id' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    DB::table('device_polling_methods')->insert($pollingMethods);
                });
        });
    }
};
