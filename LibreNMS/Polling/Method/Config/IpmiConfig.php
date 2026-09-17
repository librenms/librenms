<?php

namespace LibreNMS\Polling\Method\Config;

use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;

final class IpmiConfig extends PollingMethodConfig
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
        parent::__construct($enabled, $affectsAvailability);
    }

    public function isValid(): bool
    {
        return ! empty($this->username) && ! empty($this->password);
    }

    public static function fromPollingMethod(DevicePollingMethod $method): self
    {
        if ($method->method_type !== PollingMethodType::Ipmi) {
            throw new \Exception('Invalid polling method type');
        }

        $definition = PollingMethodType::Ipmi->definition();
        $settings = $definition->resolveValues($method->settings ?? []);
        $secretData = $method->secretData();
        $ipmiSecretData = $secretData instanceof \LibreNMS\Polling\Secrets\Data\IpmiSecretData ? $secretData : new \LibreNMS\Polling\Secrets\Data\IpmiSecretData();

        return new self(
            $method->enabled ?? true,
            $method->affects_availability ?? false,
            $ipmiSecretData->username,
            $ipmiSecretData->password,
            $ipmiSecretData->kgKey,
            ! empty($settings['hostname']) ? (string) $settings['hostname'] : ($method->device ? (string) $method->device->hostname : ''),
            (int) ($settings['port'] ?? 623),
            (int) ($settings['ciphersuite'] ?? 0),
            (int) ($settings['timeout'] ?? 3),
            (string) ($settings['type'] ?? ''),
        );
    }
}
