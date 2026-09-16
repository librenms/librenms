<?php

namespace LibreNMS\Polling\Method\Config;

use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;

class IpmiConfig extends PollingMethodConfig
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

    public static function fromModel(DevicePollingMethod $method): static
    {
        if ($method->method_type !== PollingMethodType::Ipmi) {
            throw new \Exception('Invalid polling method type');
        }

        $definition = PollingMethodType::Ipmi->definition();
        $secretDefinition = $definition->secretDefinition();

        $settings = $definition->resolveValues($method->settings ?? []);
        $resolvedData = $secretDefinition ? $secretDefinition->resolveValues($method->secret->data ?? []) : [];
        $secretData = \LibreNMS\Polling\Secrets\Data\IpmiSecretData::fromArray($resolvedData);

        return new static(
            $method->enabled,
            $method->affects_availability,
            $secretData->username,
            $secretData->password,
            $secretData->kgKey,
            ! empty($settings['hostname']) ? $settings['hostname'] : ($method->device->hostname ?? ''),
            $settings['port'] ?? 623,
            (int) ($settings['ciphersuite'] ?? 0),
            $settings['timeout'] ?? 3,
            $settings['type'] ?? '',
        );
    }
}
