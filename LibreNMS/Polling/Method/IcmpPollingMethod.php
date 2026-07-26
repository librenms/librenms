<?php

namespace LibreNMS\Polling\Method;

use App\Actions\Device\DeviceMtuTest;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Eventlog;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\PollingMethod;

readonly class IcmpPollingMethod implements PollingMethod
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

    public static function disabled(): self
    {
        return new self(
            enabled: false,
            affectsAvailability: false,
        );
    }

    public static function getSettingsSchema(): array
    {
        return [];
    }

    public static function getDefaults(): array
    {
        return [
            'affects_availability' => true,
        ];
    }

    public static function getRules(): array
    {
        return [];
    }
}
