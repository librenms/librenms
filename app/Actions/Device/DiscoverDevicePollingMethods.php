<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\HostUnreachableException;

class DiscoverDevicePollingMethods
{
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

        foreach ($enabledMethods as $method) {
            $definition = $method->method_type->definition();
            $result = $definition->discover($device, $method);

            $method->last_check_successful = $result->isSuccess();
            $method->last_checked_at = now();

            if (! $result->isSuccess()) {
                if ($pingFallback && $method->method_type !== PollingMethodType::Icmp) {
                    $method->enabled = false;
                    $candidateMethods = $candidateMethods->reject(
                        fn (DevicePollingMethod $m) => $m === $method || $m->method_type === $method->method_type
                    )->values();
                } else {
                    $exception = new HostUnreachableException((string) $device->hostname);
                    /** @var array<string, string> $reasons */
                    $reasons = (array) $result->stat('reasons', []);
                    foreach ($reasons as $version => $reason) {
                        $exception->addReason((string) $version, (string) $reason);
                    }

                    throw $exception;
                }
            }
        }

        return $candidateMethods;
    }
}
