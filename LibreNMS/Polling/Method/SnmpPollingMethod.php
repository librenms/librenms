<?php

namespace LibreNMS\Polling\Method;

use App\Facades\LibrenmsConfig;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethod;

readonly class SnmpPollingMethod implements PollingMethod
{
    public function __construct(
        public bool $enabled,
        public bool $affectsAvailability,

        // Secrets
        public string $version,
        public ?string $community,
        public ?string $authname,
        public ?string $authpass,
        public string $authlevel,
        public string $authalgo,
        public ?string $cryptopass,
        public string $cryptoalgo,
        public ?string $context,

        // Settings
        public string $transport,
        public int $port,
        public int $timeout,
        public int $retries,
        public int $maxRepeaters,
        public int $maxOid,
    ) {}

    public static function fromModel(DevicePollingMethod $method): self
    {
        if ($method->method_type !== PollingMethodType::Snmp) {
            throw new \Exception('Invalid polling method type');
        }

        $device = $method->device;
        $secretData = $method->secret?->data ?? [];

        $version = $secretData['version'] ?? 'v2c';
        $community = $secretData['community'] ?? null;
        $authlevel = $secretData['authlevel'] ?? 'noAuthNoPriv';
        $authname = $secretData['authname'] ?? null;
        $authpass = $secretData['authpass'] ?? null;
        $authalgo = $secretData['authalgo'] ?? 'SHA';
        $cryptopass = $secretData['cryptopass'] ?? null;
        $cryptoalgo = $secretData['cryptoalgo'] ?? 'AES';
        $context = $secretData['context'] ?? null;

        $transport = $method->settings['transport'] ?? 'udp';
        $port = (int) ($method->settings['port'] ?? 161);
        $timeout = (int) ($method->settings['timeout'] ?? LibrenmsConfig::get('snmp.timeout', 3));
        $retries = (int) ($method->settings['retries'] ?? LibrenmsConfig::get('snmp.retries', 1));
        $maxRepeaters = (int) (($method->settings['max_repeaters'] ?? null) ?: LibrenmsConfig::getOsSetting($device?->os, 'snmp.max_repeaters', LibrenmsConfig::get('snmp.max_repeaters', 0)));
        $maxOid = (int) (($method->settings['max_oid'] ?? null) ?: LibrenmsConfig::getOsSetting($device?->os, 'snmp_max_oid', LibrenmsConfig::get('snmp.max_oid', 10)));

        return new self(
            enabled: $method->enabled,
            affectsAvailability: $method->affects_availability,
            version: $version,
            community: $community,
            authname: $authname,
            authpass: $authpass,
            authlevel: $authlevel,
            authalgo: $authalgo,
            cryptopass: $cryptopass,
            cryptoalgo: $cryptoalgo,
            context: $context,
            transport: $transport,
            port: $port,
            timeout: $timeout,
            retries: $retries,
            maxRepeaters: $maxRepeaters,
            maxOid: $maxOid,
        );
    }

    public function toNetSnmpOptions(?string $context = null): array
    {
        $options = ['-' . $this->version];

        if ($this->version === 'v3') {
            if ($this->authname !== null) {
                array_push($options, '-u', $this->authname);
            }

            array_push($options, '-l', $this->authlevel);

            if (in_array($this->authlevel, ['authNoPriv', 'authPriv'])) {
                array_push($options, '-a', $this->authalgo);

                if ($this->authpass !== null) {
                    array_push($options, '-A', $this->authpass);
                }
            }

            if ($this->authlevel === 'authPriv') {
                array_push($options, '-x', $this->cryptoalgo);

                if ($this->cryptopass !== null) {
                    array_push($options, '-X', $this->cryptopass);
                }
            }

            $resolvedContext = $context ?? $this->context;
            if ($resolvedContext !== null) {
                array_push($options, '-n', $resolvedContext);
            }
        } else {
            if ($this->community !== null) {
                array_push($options, '-c', $this->community);
            }
        }

        return $options;
    }

    public static function disabled(): self
    {
        return new self(
            enabled: false,
            affectsAvailability: false,
            version: 'v2c',
            community: null,
            authname: null,
            authpass: null,
            authlevel: 'noAuthNoPriv',
            authalgo: 'SHA',
            cryptopass: null,
            cryptoalgo: 'AES',
            context: null,
            transport: 'udp',
            port: 161,
            timeout: 3,
            retries: 1,
            maxRepeaters: 0,
            maxOid: 10,
        );
    }

    public static function fromSecret(\LibreNMS\Polling\Secrets\SnmpSecretData $secret): self
    {
        return new self(
            enabled: true,
            affectsAvailability: false,
            version: $secret->version,
            community: $secret->community,
            authname: $secret->authname,
            authpass: $secret->authpass,
            authlevel: $secret->authlevel,
            authalgo: $secret->authalgo,
            cryptopass: $secret->cryptopass,
            cryptoalgo: $secret->cryptoalgo,
            context: $secret->context,
            transport: 'udp',
            port: 161,
            timeout: 3,
            retries: 1,
            maxRepeaters: 0,
            maxOid: 10,
        );
    }

    public static function getSettingsSchema(): array
    {
        return [
            'transport' => [
                'type' => 'select',
                'options' => [
                    'udp' => 'UDP',
                    'tcp' => 'TCP',
                    'udp6' => 'UDP6',
                    'tcp6' => 'TCP6',
                ],
            ],
            'port' => [
                'type' => 'number',
            ],
            'timeout' => [
                'type' => 'number',
            ],
            'retries' => [
                'type' => 'number',
            ],
            'max_repeaters' => [
                'type' => 'number',
            ],
            'max_oid' => [
                'type' => 'number',
            ],
        ];
    }

    public static function getDefaults(): array
    {
        return [
            'affects_availability' => true,
            'transport' => 'default',
            'port' => 161,
            'timeout' => 3,
            'retries' => 1,
            'max_repeaters' => 0,
            'max_oid' => 10,
        ];
    }

    public static function getRules(): array
    {
        return [
            'transport' => ['required', 'string', 'in:udp,tcp,udp6,tcp6'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'timeout' => ['nullable', 'integer', 'min:1', 'max:60'],
            'retries' => ['nullable', 'integer', 'min:0', 'max:10'],
            'max_repeaters' => ['nullable', 'integer', 'min:0', 'max:30'],
            'max_oid' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
