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

    public static function fromPollingMethod(DevicePollingMethod $method): static
    {
        if ($method->method_type !== PollingMethodType::Ipmi) {
            throw new \Exception('Invalid polling method type');
        }

        $definition = PollingMethodType::Ipmi->definition();
        $settings = $definition->resolveValues($method->settings ?? []);
        $secretData = $method->secretData();
        $ipmiSecretData = $secretData instanceof \LibreNMS\Polling\Secrets\Data\IpmiSecretData ? $secretData : new \LibreNMS\Polling\Secrets\Data\IpmiSecretData();

        return new static(
            $method->enabled,
            $method->affects_availability,
            $ipmiSecretData->username,
            $ipmiSecretData->password,
            $ipmiSecretData->kgKey,
            ! empty($settings['hostname']) ? $settings['hostname'] : ($method->device->hostname ?? ''),
            (int) ($settings['port'] ?? 623),
            (int) ($settings['ciphersuite'] ?? 0),
            (int) ($settings['timeout'] ?? 3),
            (string) ($settings['type'] ?? ''),
        );
    }
}
