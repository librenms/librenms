<?php

namespace App\Listeners;

use App\Events\SettingChanged;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

class RegenerateDeviceDisplayNames
{
    /**
     * Handle the event.
     */
    public function handle(SettingChanged $event): void
    {
        Device::withoutEvents(function () {
            Device::whereNull('display_template')
                ->orWhere('display_template', '')
                ->select(['device_id', 'display_template', 'hostname', 'sysName', 'ip', 'overwrite_ip'])
                ->chunkById(500, function ($devices) {
                    foreach ($devices as $device) {
                        try {
                            $device->regenerateDisplayName();
                            $device->timestamps = false;
                            $device->save();
                        } catch (\Throwable $e) {
                            Log::error("Failed to regen device {$device->device_id}: " . $e->getMessage());
                        }
                    }
                });
        });
    }
}
