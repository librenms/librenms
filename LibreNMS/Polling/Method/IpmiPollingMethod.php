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
            $method->settings['hostname'] ?? '',
            (int) ($method->settings['port'] ?? 623),
            (int) ($method->settings['ciphersuite'] ?? 0),
            (int) ($method->settings['timeout'] ?? 3),
            $method->settings['type'] ?? '',
        );
    }

    public static function save(
        \App\Models\Device $device,
        array $settings = [],
        array $secretData = [],
        bool $enabled = true,
        bool $affectsAvailability = false,
    ): DevicePollingMethod {
        $method = DevicePollingMethod::with('secret')
            ->firstOrNew([
                'device_id' => $device->device_id,
                'method_type' => PollingMethodType::Ipmi,
            ]);

        $method->enabled = $enabled;
        $method->affects_availability = $affectsAvailability;

        if (! empty($settings)) {
            $method->settings = array_merge($method->settings ?? [], $settings);
        }

        if (! empty($secretData)) {
            if ($method->secret) {
                $method->secret->update(['data' => array_merge($method->secret->data, $secretData)]);
            } else {
                $secret = \App\Models\Secret::create([
                    'description' => 'IPMI ' . $device->hostname,
                    'secret_type' => \LibreNMS\Enum\SecretType::Ipmi,
                    'default' => false,
                    'data' => $secretData,
                ]);
                $method->secret_id = $secret->id;
            }
        }

        $method->save();
        $device->load('pollingMethods');

        return $method;
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
