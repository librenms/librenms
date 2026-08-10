<?php

use App\Models\Device;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Device::withoutEvents(function () {
            Device::select(['device_id', 'display_template', 'hostname', 'sysName', 'ip', 'overwrite_ip'])
                ->chunkById(500, function ($devices) {
                    foreach ($devices as $device) {
                        try {
                            $device->regenerateDisplayName();
                            $device->timestamps = false; // just to be safe, disable timestamps
                            $device->save();
                        } catch (\Throwable $e) {
                            Log::error("Failed to regen device {$device->device_id}: " . $e->getMessage());
                        }
                    }
                });
        });
    }
};
