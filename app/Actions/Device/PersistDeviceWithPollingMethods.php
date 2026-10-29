<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LibreNMS\Polling\Method\PollingMethodRegistry;

class PersistDeviceWithPollingMethods
{
    public function __construct(
        private readonly PollingMethodRegistry $registry,
    ) {
    }

    /**
     * @param  Collection<int, DevicePollingMethod>|null  $pollingMethods
     *
     * @throws ValidationException
     */
    public function execute(Device $device, ?Collection $pollingMethods = null): bool
    {
        foreach ($pollingMethods ?? [] as $deviceMethod) {
            $this->registry->get($deviceMethod->method_type)->validate($deviceMethod);
        }

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
