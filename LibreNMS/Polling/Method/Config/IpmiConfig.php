<?php

namespace LibreNMS\Polling\Method\Config;

use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Secrets\Data\IpmiSecretData;

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

    public static function fromPollingMethod(DevicePollingMethod $deviceMethod): self
    {
        if ($deviceMethod->method_type !== PollingMethodType::Ipmi) {
            throw new \Exception('Invalid polling method type');
        }

        $settings = $deviceMethod->settings ?? [];
        $secretData = $deviceMethod->secret ? IpmiSecretData::fromArray($deviceMethod->secret->data ?? []) : new IpmiSecretData();

        return new self(
            $deviceMethod->enabled ?? true,
            $deviceMethod->affects_availability ?? false,
            $secretData->username,
            $secretData->password,
            $secretData->kgKey,
            ! empty($settings['hostname']) ? (string) $settings['hostname'] : ($deviceMethod->device ? (string) $deviceMethod->device->hostname : ''),
            (int) ($settings['port'] ?? 623),
            (int) ($settings['ciphersuite'] ?? 0),
            (int) ($settings['timeout'] ?? 3),
            (string) ($settings['type'] ?? ''),
        );
    }
}
