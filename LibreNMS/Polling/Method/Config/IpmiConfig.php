<?php

namespace LibreNMS\Polling\Method\Config;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Data\Source\Ipmitool;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodConfigInterface;

readonly class IpmiConfig implements PollingMethodConfigInterface
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

        $definition = PollingMethodType::Ipmi->definition();
        $secretDefinition = $definition->secretDefinition();

        $settings = $definition->resolveValues($method->settings ?? []);
        $secretData = $secretDefinition ? $secretDefinition->resolveValues($method->secret?->data ?? []) : [];

        return new static(
            $method->enabled,
            $method->affects_availability,
            $secretData['username'] ?? '',
            $secretData['password'] ?? '',
            $secretData['kg_key'] ?? '',
            $settings['hostname'] ?? '',
            $settings['port'] ?? 623,
            (int) ($settings['ciphersuite'] ?? 0),
            $settings['timeout'] ?? 3,
            $settings['type'] ?? '',
        );
    }
}
