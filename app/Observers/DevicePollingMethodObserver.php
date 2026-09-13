<?php

namespace App\Observers;

use App\Models\DevicePollingMethod;

class DevicePollingMethodObserver
{
    public function saved(DevicePollingMethod $method): void
    {
        if (! $method->enabled && $method->device_id) {
            $device = $method->device;
            if (! $device) {
                return;
            }

            $typeValue = $method->method_type->value;
            $reasons = collect(explode(',', (string) $device->status_reason))
                ->reject(fn ($v) => $v === $typeValue)
                ->filter()
                ->implode(',');

            if ($device->status_reason !== $reasons) {
                $device->status_reason = $reasons;
                if ($device->status == 0 && empty($reasons)) {
                    $device->status = 1;
                }
                $device->save();
            }
        }
    }
}
