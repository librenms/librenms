<?php

namespace LibreNMS\Polling\Method;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Data\Source\Ipmitool;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethod;

readonly class IpmiPollingMethod implements PollingMethod
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
            $method->settings['hostname'] ?? '',
            (int) ($method->settings['port'] ?? 623),
            (int) ($method->settings['ciphersuite'] ?? 0),
            (int) ($method->settings['timeout'] ?? 3),
            $method->settings['type'] ?? '',
        );
    }

    public static function disabled(): self
    {
        return new self(
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

    public static function getSettingsSchema(): array
    {
        return [
            'hostname' => [
                'type' => 'text',
            ],
            'port' => [
                'type' => 'number',
            ],
            'ciphersuite' => [
                'type' => 'text',
            ],
            'timeout' => [
                'type' => 'number',
            ],
        ];
    }

    public static function getDefaults(): array
    {
        return [
            'affects_availability' => false,
            'hostname' => '',
            'port' => 623,
            'ciphersuite' => '',
            'timeout' => 3,
        ];
    }

    public static function getRules(): array
    {
        return [
            'hostname' => ['required', 'string'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'ciphersuite' => ['nullable', 'string'],
            'timeout' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
