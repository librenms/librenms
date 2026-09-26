<?php

namespace App\Observers;

use App\Models\DevicePollingMethod;

class DevicePollingMethodObserver
{
    public function saved(DevicePollingMethod $deviceMethod): void
    {
        if (! $deviceMethod->enabled && $deviceMethod->device_id) {
            $this->cleanupStatusReason($deviceMethod);
        }
    }

    public function deleted(DevicePollingMethod $deviceMethod): void
    {
        if ($deviceMethod->device_id) {
            $this->cleanupStatusReason($deviceMethod);
        }
    }

    private function cleanupStatusReason(DevicePollingMethod $deviceMethod): void
    {
        $device = $deviceMethod->device;
        if (! $device) {
            return;
        }

        $typeValue = $deviceMethod->method_type->value;
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
