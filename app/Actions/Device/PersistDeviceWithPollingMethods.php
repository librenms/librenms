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
                foreach ($pollingMethods as $method) {
                    $method->device_id = $device->device_id;

                    if ($method->relationLoaded('secret') && $method->secret) {
                        $secret = $method->secret;
                        if (! $secret->exists) {
                            if (empty($secret->description)) {
                                $secret->description = strtoupper($method->method_type->value) . ' ' . $device->hostname;
                            }
                            $secret->save();
                        }
                        $method->secret_id = $secret->id;
                    }

                    $method->save();
                }

                $device->setRelation('pollingMethods', $pollingMethods);
            }

            return $saved;
        });
    }
}
