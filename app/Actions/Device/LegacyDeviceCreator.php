<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;

class LegacyDeviceCreator
{
    private ?Device $device = null;
    private readonly BuildDefaultPollingMethods $builder;

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
        $this->builder = resolve(BuildDefaultPollingMethods::class);
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
        $methods = collect([
            $this->builder->buildMethod($device, PollingMethodType::Icmp, [
                'affects_availability' => $this->ping_only || $this->ping_fallback,
            ]),
        ]);

        if (! $this->ping_only) {
            $methods->push($this->builder->buildMethod($device, PollingMethodType::Snmp, [
                'settings' => array_filter([
                    'port' => $this->port,
                    'transport' => $this->transport,
                    'port_association_mode' => $this->port_association_mode,
                ], fn ($v) => $v !== null),
                'secret_data' => $this->snmpSecretData()?->toArray(),
            ]));
        }

        return $methods;
    }

    /**
     * Explicit SNMP credentials, or null to try the default credentials.
     */
    private function snmpSecretData(): ?SnmpSecretData
    {
        $hasCredentials = $this->snmpver !== null
            || $this->community !== null
            || $this->authpass !== null
            || $this->cryptopass !== null
            || ($this->authname !== null && $this->authname !== 'root')
            || ($this->authalgo !== null && $this->authalgo !== 'MD5')
            || ($this->cryptoalgo !== null && $this->cryptoalgo !== 'AES');

        if (! $hasCredentials) {
            return null;
        }

        return new SnmpSecretData(
            version: $this->snmpver ?: 'v2c',
            community: $this->community,
            authlevel: $this->authlevel ?: (($this->authpass ? 'auth' : 'noAuth') . (($this->cryptopass && $this->authpass) ? 'Priv' : 'NoPriv')),
            authname: $this->authname ?: 'root',
            authpass: $this->authpass,
            authalgo: $this->authalgo ?: 'MD5',
            cryptoalgo: $this->cryptoalgo ?: 'AES',
            cryptopass: $this->cryptopass,
        );
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
