<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\Eventlog;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\Severity;

readonly class CheckDeviceAvailability
{
    public function __construct(
        private SetDeviceAvailability $setDeviceAvailability,
    ) {
    }

    public function execute(Device $device, bool $commit = false): bool
    {
        $enabledPollingMethods = $device->pollingMethods->filter(fn ($m) => $m->enabled);

        foreach ($enabledPollingMethods as $method) {
            $definition = $method->method_type->definition();
            $result = $definition->probe()->check($device);

            $method->last_check_successful = $result->isSuccess();
            $method->last_checked_at = now();

            if ($method->method_type === PollingMethodType::Icmp) {
                if ($result->stat('duplicates')) {
                    Eventlog::log('Duplicate ICMP response detected! This could indicate a network issue.', $device, 'icmp', Severity::Warning);
                }

                $fpingStatus = $result->stat('fping_status');
                if ($commit && $fpingStatus) {
                    $fpingStatus->saveStats($device);
                }

                $mtuStatus = $result->stat('mtu_status');
                if ($result->isSuccess() && $mtuStatus !== null) {
                    $device->mtu_status = $mtuStatus;
                }
            }
        }

        $this->setDeviceAvailability->execute($device, $commit);

        if ($commit) {
            $enabledPollingMethods->each->save();
            $device->save(); // confirm device is saved
        }

        return $device->status;
    }
}
