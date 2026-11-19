<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\HostUnreachableException;

readonly class DiscoverDevicePollingMethods
{
    public function __construct(
        private DiscoverPollingMethod $discoverMethod,
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
            $result = $this->discoverMethod->execute($device, $deviceMethod);

            $deviceMethod->last_check_successful = $result->isSuccess();

            if (! $result->isSuccess()) {
                if ($pingFallback && $deviceMethod->method_type !== PollingMethodType::Icmp) {
                    $candidateMethods = $candidateMethods->reject(fn (DevicePollingMethod $m) => $m === $deviceMethod)->values();
                } else {
                    $exception = new HostUnreachableException(__('poller.reachability_failed', [
                        'hostname' => (string) $device->hostname,
                        'method' => $deviceMethod->method_type->label(),
                    ]), $deviceMethod->method_type);
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
