<?php

namespace LibreNMS\Polling\Method\Config;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodConfigInterface;
use SnmpQuery;

readonly class SnmpConfig implements PollingMethodConfigInterface
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

        $definition = PollingMethodType::Snmp->definition();
        $secretDefinition = $definition->secretDefinition();

        $settings = $definition->resolveValues($method->settings ?? []);
        $secretData = $secretDefinition->resolveValues($method->secret->data ?? []);

        return new static(
            enabled: $method->enabled,
            affectsAvailability: $method->affects_availability,
            version: $secretData['version'],
            community: $secretData['community'] ?? null,
            authname: $secretData['authname'] ?? null,
            authpass: $secretData['authpass'] ?? null,
            authlevel: $secretData['authlevel'],
            authalgo: $secretData['authalgo'],
            cryptopass: $secretData['cryptopass'] ?? null,
            cryptoalgo: $secretData['cryptoalgo'],
            context: $secretData['context'] ?? null,
            transport: $settings['transport'],
            port: $settings['port'],
            timeout: $settings['timeout'],
            retries: $settings['retries'],
            maxRepeaters: $settings['max_repeaters'],
            maxOid: $settings['max_oid'],
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
}
