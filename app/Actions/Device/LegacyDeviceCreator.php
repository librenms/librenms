<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretMode;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Secrets\Data\SnmpCommunitySecretData;
use LibreNMS\Polling\Secrets\Data\SnmpV3SecretData;
use LibreNMS\Polling\Secrets\Definitions\SnmpV3SecretDefinition;

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
        $secretType = $this->snmpSecretType();
        $secretData = $this->snmpSecretData($secretType);

        return [
            // a ping only device is always pinged
            PollingMethodType::Icmp->value => ['active' => $this->ping_only || LibrenmsConfig::get('icmp_check', true)],
            PollingMethodType::Snmp->value => [
                'active' => ! $this->ping_only,
                'settings' => array_filter([
                    // a given community version is only tried with that version, without one v2c then v1 are tried
                    'version' => in_array($this->snmpver, ['v1', 'v2c'], true) ? $this->snmpver : null,
                    'port' => $this->port,
                    'transport' => $this->transport,
                    'port_association_mode' => $this->port_association_mode,
                ], fn ($v) => $v !== null),
                'secret_mode' => $secretData ? SecretMode::New->value : SecretMode::Default->value,
                'secret_type' => $secretType?->value,
                'secret_data' => $secretData?->toArray(),
            ],
        ];
    }

    /**
     * The type of the new secret, or of the default credentials to try without one. Null when nothing says which.
     */
    private function snmpSecretType(): ?SecretType
    {
        if ($this->snmpver === 'v3') {
            return SecretType::SnmpV3;
        }

        if ($this->snmpver || filled($this->community)) {
            return SecretType::SnmpCommunity;
        }

        return $this->hasV3Credentials() ? SecretType::SnmpV3 : null;
    }

    /**
     * Explicit SNMP credentials of the type, or null to try the default credentials.
     * Any given value is explicit, even one that matches a default.
     * v3 values without a community are not credentials for v1 or v2c.
     */
    private function snmpSecretData(?SecretType $secretType): SnmpCommunitySecretData|SnmpV3SecretData|null
    {
        if ($secretType === SecretType::SnmpCommunity && filled($this->community)) {
            return new SnmpCommunitySecretData((string) $this->community);
        }

        if ($secretType === SecretType::SnmpV3 && ($this->hasV3Credentials() || filled($this->community))) {
            return new SnmpV3SecretData(
                authname: $this->authname ?: 'root',
                authlevel: $this->authlevel ?: (($this->authpass ? 'auth' : 'noAuth') . (($this->cryptopass && $this->authpass) ? 'Priv' : 'NoPriv')),
                authpass: $this->authpass,
                authalgo: $this->authalgo ?: SnmpV3SecretDefinition::DEFAULT_AUTHALGO,
                cryptoalgo: $this->cryptoalgo ?: SnmpV3SecretDefinition::DEFAULT_CRYPTOALGO,
                cryptopass: $this->cryptopass,
            );
        }

        return null;
    }

    private function hasV3Credentials(): bool
    {
        return filled($this->authname)
            || filled($this->authlevel)
            || filled($this->authpass)
            || filled($this->authalgo)
            || filled($this->cryptopass)
            || filled($this->cryptoalgo);
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
