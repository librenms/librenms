<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;
use LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition;

class LegacyDeviceCreator
{
    private ?Device $device = null;

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
        return resolve(BuildDefaultPollingMethods::class)->execute($device, ['methods' => $this->pollingMethodsInput()]);
    }

    /**
     * The flat legacy arguments as polling method input, the same shape the add device form sends.
     *
     * @return array<string, array<string, mixed>>
     */
    public function pollingMethodsInput(): array
    {
        return [
            PollingMethodType::Icmp->value => ['active' => true],
            PollingMethodType::Snmp->value => [
                'active' => ! $this->ping_only,
                'settings' => array_filter([
                    'port' => $this->port,
                    'transport' => $this->transport,
                    'port_association_mode' => $this->port_association_mode,
                ], fn ($v) => $v !== null),
                'secret_data' => $this->snmpSecretData()?->toArray(),
            ],
        ];
    }

    /**
     * Explicit SNMP credentials, only a version to try the default credentials for that version, or null to try all default credentials.
     * Any given value is explicit, even one that matches a default.
     */
    private function snmpSecretData(): ?SnmpSecretData
    {
        $hasV3Credentials = filled($this->authname)
            || filled($this->authlevel)
            || filled($this->authpass)
            || filled($this->authalgo)
            || filled($this->cryptopass)
            || filled($this->cryptoalgo);

        if (blank($this->community) && ! $hasV3Credentials) {
            return $this->snmpver ? new SnmpSecretData(version: $this->snmpver) : null;
        }

        return new SnmpSecretData(
            version: $this->snmpver ?: (blank($this->community) ? 'v3' : SnmpSecretDefinition::DEFAULT_VERSION),
            community: $this->community,
            authlevel: $this->authlevel ?: (($this->authpass ? 'auth' : 'noAuth') . (($this->cryptopass && $this->authpass) ? 'Priv' : 'NoPriv')),
            authname: $this->authname ?: 'root',
            authpass: $this->authpass,
            authalgo: $this->authalgo ?: SnmpSecretDefinition::DEFAULT_AUTHALGO,
            cryptoalgo: $this->cryptoalgo ?: SnmpSecretDefinition::DEFAULT_CRYPTOALGO,
            cryptopass: $this->cryptopass,
        );
    }

    /**
     * @throws \LibreNMS\Exceptions\HostExistsException
     * @throws \LibreNMS\Exceptions\HostUnreachableException
     * @throws \LibreNMS\Exceptions\SnmpVersionUnsupportedException
     * @throws \LibreNMS\Exceptions\MissingSecretException
     */
    public function execute(): bool
    {
        $device = $this->getDevice();

        return resolve(ValidateDeviceAndCreate::class)->execute(
            $device,
            $this->getPollingMethods($device),
            force: $this->force,
            pingFallback: $this->ping_fallback,
        );
    }
}
