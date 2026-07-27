<?php

namespace LibreNMS\Polling\Method;

use App\Actions\Device\DeviceMtuTest;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Eventlog;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\PollingMethodInterface;

readonly class IcmpPollingMethod implements PollingMethodInterface
{
    public function __construct(
        public bool $enabled,
        public bool $affectsAvailability,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isAvailable(Device $device, bool $commit = false): bool
    {
        $fping = app(Fping::class);
        $status = $fping->ping($device->pollerTarget(), $device->ipFamily());

        if ($status->duplicates > 0) {
            Eventlog::log('Duplicate ICMP response detected! This could indicate a network issue.', $device, 'icmp', Severity::Warning);
            $status->ignoreFailure();
        }

        if ($commit) {
            $status->saveStats($device);
        }

        if ($status->isAlive()) {
            $device->mtu_status = app(DeviceMtuTest::class)->execute($device);
        }

        return $status->isAlive();
    }

    public static function fromModel(DevicePollingMethod $method): static
    {
        if ($method->method_type !== PollingMethodType::Icmp) {
            throw new \Exception('Invalid polling method type');
        }

        return new static(
            enabled: $method->enabled,
            affectsAvailability: $method->affects_availability,
        );
    }

    public static function save(
        \App\Models\Device $device,
        array $settings = [],
        array $secretData = [],
        bool $enabled = true,
        bool $affectsAvailability = true,
    ): DevicePollingMethod {
        $method = DevicePollingMethod::firstOrNew([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
        ]);

        $method->enabled = $enabled;
        $method->affects_availability = $affectsAvailability;
        $method->save();
        $device->load('pollingMethods');

        return $method;
    }

    public static function disabled(): static
    {
        return new static(
            enabled: false,
            affectsAvailability: false,
        );
    }
}
