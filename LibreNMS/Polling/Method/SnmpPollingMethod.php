<?php

namespace LibreNMS\Polling\Method;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodInterface;
use SnmpQuery;

readonly class SnmpPollingMethod implements PollingMethodInterface
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
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isAvailable(Device $device, bool $commit = false): bool
    {
        $response = SnmpQuery::device($device)->get('SNMPv2-MIB::sysObjectID.0');

        return $response->getExitCode() === 0 || $response->getExitCode() === 2 || $response->isValid();
    }

    public static function fromModel(DevicePollingMethod $method): static
    {
        if ($method->method_type !== PollingMethodType::Snmp) {
            throw new \Exception('Invalid polling method type');
        }

        $device = $method->device;
        $secret = $method->secret;
        $secretData = $secret ? $secret->data : [];

        return new static(
            enabled: $method->enabled,
            affectsAvailability: $method->affects_availability,
            version: $secretData['version'] ?? 'v2c',
            community: $secretData['community'] ?? null,
            authname: $secretData['authname'] ?? null,
            authpass: $secretData['authpass'] ?? null,
            authlevel: $secretData['authlevel'] ?? 'noAuthNoPriv',
            authalgo: $secretData['authalgo'] ?? 'SHA',
            cryptopass: $secretData['cryptopass'] ?? null,
            cryptoalgo: $secretData['cryptoalgo'] ?? 'AES',
            context: $secretData['context'] ?? null,
            transport: $method->settings['transport'] ?? 'udp',
            port: (int) ($method->settings['port'] ?? 161),
            timeout: (int) ($method->settings['timeout'] ?? LibrenmsConfig::get('snmp.timeout', 3)),
            retries: (int) ($method->settings['retries'] ?? LibrenmsConfig::get('snmp.retries', 1)),
            maxRepeaters: (int) (($method->settings['max_repeaters'] ?? null) ?: LibrenmsConfig::getOsSetting($device?->os, 'snmp.max_repeaters', LibrenmsConfig::get('snmp.max_repeaters', 0))),
            maxOid: (int) (($method->settings['max_oid'] ?? null) ?: LibrenmsConfig::getOsSetting($device?->os, 'snmp_max_oid', LibrenmsConfig::get('snmp.max_oid', 10))),
        );
    }

    /**
     * @return array<int, string>
     */
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

    public static function disabled(): static
    {
        return new static(
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
}
