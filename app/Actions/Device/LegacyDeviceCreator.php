<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;

class LegacyDeviceCreator
{
    private ?Device $device = null;
    private readonly PollingMethodRegistry $pollingMethods;

    public function __construct(
        public string $hostname,
        public ?string $display_template = null,
        public int $poller_group = 0,
        public ?string $overwrite_ip = null,
        public ?int $location_id = null,
        public bool $override_sysLocation = false,
        public ?string $sysName = null,
        public ?string $hardware = null,
        public ?string $os = null,
        public bool $ping_only = false,
        public ?string $snmpver = null,
        public ?string $community = null,
        public ?int $port = null,
        public ?string $transport = null,
        public ?string $port_association_mode = null,
        public ?string $authname = null,
        public ?string $authpass = null,
        public ?string $authalgo = null,
        public ?string $cryptopass = null,
        public ?string $cryptoalgo = null,
        public ?string $authlevel = null,
        public bool $force = false,
        public bool $ping_fallback = false,
    ) {
        $this->pollingMethods = resolve(PollingMethodRegistry::class);
    }

    public function getDevice(): Device
    {
        $this->device ??= new Device(array_filter([
            'hostname' => $this->hostname,
            'display_template' => $this->display_template,
            'poller_group' => $this->poller_group,
            'overwrite_ip' => $this->overwrite_ip,
            'location_id' => $this->location_id,
            'override_sysLocation' => $this->override_sysLocation ? 1 : 0,
            'sysName' => $this->sysName,
            'hardware' => $this->hardware,
            'os' => $this->os,
        ], fn ($value) => $value !== null));

        return $this->device;
    }

    /**
     * @return Collection<int, DevicePollingMethod>
     */
    public function getPollingMethods(Device $device): Collection
    {
        $methods = collect();

        $icmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => $this->ping_only || $this->ping_fallback,
            'settings' => [],
        ]);
        $icmpMethod->setRelation('device', $device);
        $methods->push($icmpMethod);

        if (! $this->ping_only) {
            $settings = array_filter([
                'port' => $this->port,
                'transport' => $this->transport,
                'port_association_mode' => $this->port_association_mode,
            ], fn ($v) => $v !== null);

            $snmpMethodDef = $this->pollingMethods->require(PollingMethodType::Snmp);

            $hasCredentials = $this->snmpver !== null
                || $this->community !== null
                || $this->authpass !== null
                || $this->cryptopass !== null
                || ($this->authname !== null && $this->authname !== 'root')
                || ($this->authalgo !== null && $this->authalgo !== 'MD5')
                || ($this->cryptoalgo !== null && $this->cryptoalgo !== 'AES');

            $secret = null;
            if ($hasCredentials) {
                $authlevel = $this->authlevel ?: (($this->authpass ? 'auth' : 'noAuth') . (($this->cryptopass && $this->authpass) ? 'Priv' : 'NoPriv'));
                $secretData = new SnmpSecretData(
                    version: $this->snmpver ?: 'v2c',
                    community: $this->community,
                    authlevel: $authlevel,
                    authname: $this->authname ?: 'root',
                    authpass: $this->authpass,
                    authalgo: $this->authalgo ?: 'MD5',
                    cryptoalgo: $this->cryptoalgo ?: 'AES',
                    cryptopass: $this->cryptopass,
                );

                $secret = new Secret([
                    'secret_type' => SecretType::Snmp->value,
                    'description' => 'SNMP ' . $device->hostname,
                    'data' => $secretData->toArray(),
                ]);
            }

            $snmpMethod = new DevicePollingMethod([
                'method_type' => PollingMethodType::Snmp,
                'enabled' => true,
                'affects_availability' => $snmpMethodDef->defaultAffectsAvailability(),
                'settings' => $snmpMethodDef->filterOverrides($settings),
            ]);
            $snmpMethod->setRelation('device', $device);
            if ($secret !== null) {
                $snmpMethod->setRelation('secret', $secret);
            }

            $methods->push($snmpMethod);
        }

        return $methods;
    }

    public function createValidator(): ValidateDeviceAndCreate
    {
        $device = $this->getDevice();
        $methods = $this->getPollingMethods($device);

        return new ValidateDeviceAndCreate(
            device: $device,
            pollingMethods: $methods,
            force: $this->force,
            ping_fallback: $this->ping_fallback,
        );
    }

    public function execute(): bool
    {
        return $this->createValidator()->execute();
    }
}
