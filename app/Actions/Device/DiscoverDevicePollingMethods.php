<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Polling\Method\PollingMethodRegistry;

readonly class DiscoverDevicePollingMethods
{
    public function __construct(
        private PollingMethodRegistry $pollingMethods,
    ) {
    }

    /**
     * Discover and validate candidate polling methods for a device.
     *
     * @param  Collection<int, DevicePollingMethod>  $candidateMethods
     * @return Collection<int, DevicePollingMethod>
     *
     * @throws HostUnreachableException
     */
    public function execute(Device $device, Collection $candidateMethods, bool $pingFallback = false): Collection
    {
        $enabledMethods = $candidateMethods->filter(fn (DevicePollingMethod $m) => $m->enabled);

        foreach ($enabledMethods as $deviceMethod) {
            $method = $this->pollingMethods->require($deviceMethod->method_type);
            $result = $method->discover($device, $deviceMethod);

            $deviceMethod->last_check_successful = $result->isSuccess();
            $deviceMethod->last_checked_at = now();

            if (! $result->isSuccess()) {
                if ($pingFallback && $deviceMethod->method_type !== PollingMethodType::Icmp) {
                    $candidateMethods = $candidateMethods->reject(fn (DevicePollingMethod $m) => $m === $deviceMethod)->values();
                } else {
                    $exception = new HostUnreachableException((string) $device->hostname);
                    foreach ($result->reasons() as $reason) {
                        $exception->addReason($reason);
                    }

                    throw $exception;
                }
            }
        }

        return $candidateMethods;
    }
}
