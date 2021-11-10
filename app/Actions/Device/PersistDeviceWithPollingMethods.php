<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PersistDeviceWithPollingMethods
{
    /**
     * @param  Collection<int, DevicePollingMethod>|null  $pollingMethods
     */
    public function execute(Device $device, ?Collection $pollingMethods = null): bool
    {
        return DB::transaction(function () use ($device, $pollingMethods): bool {
            $saved = $device->save();

            if ($saved && $pollingMethods !== null) {
                foreach ($pollingMethods as $deviceMethod) {
                    $deviceMethod->device_id = $device->device_id;

                    if ($deviceMethod->relationLoaded('secret') && $deviceMethod->secret) {
                        $deviceMethod->secret->save();
                        $deviceMethod->secret_id = $deviceMethod->secret->id;
                    }

                    $deviceMethod->save();
                }

                $device->setRelation('pollingMethods', $pollingMethods);
            }

            return $saved;
        });
    }
}
