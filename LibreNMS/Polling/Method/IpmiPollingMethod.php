<?php

namespace LibreNMS\Polling\Method;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Data\Source\Ipmitool;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodInterface;

readonly class IpmiPollingMethod implements PollingMethodInterface
{
    public function __construct(
        public bool $enabled,
        public bool $affectsAvailability,
        public string $username,
        public string $password,
        public string $kgKey,
        public string $hostname,
        public int $port,
        public int $cipherSuite,
        public int $timeout,
        public string $type,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isAvailable(Device $device, bool $commit = false): bool
    {
        $ipmi = Ipmitool::init($device);

        if (! $ipmi) {
            return false;
        }

        try {
            $ipmi->command(['power', 'status']);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function fromModel(DevicePollingMethod $method): static
    {
        if ($method->method_type !== PollingMethodType::Ipmi) {
            throw new \Exception('Invalid polling method type');
        }

        return new static(
            $method->enabled,
            $method->affects_availability,
            $method->secret?->data['username'] ?? '',
            $method->secret?->data['password'] ?? '',
            $method->secret?->data['kg_key'] ?? '',
            $method->settings['hostname'],
            (int) $method->settings['port'],
            (int) $method->settings['ciphersuite'],
            (int) $method->settings['timeout'],
            $method->settings['type'] ?? '',
        );
    }

    public static function disabled(): static
    {
        return new static(
            false,
            false,
            '',
            '',
            '',
            '',
            0,
            0,
            0,
            '',
        );
    }
}
