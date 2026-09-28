<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Eventlog;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Enum\SecretType;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\MissingSecretException;
use LibreNMS\Modules\Core;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Definitions\SnmpDefinition;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SnmpQuery;

/**
 * @extends PollingMethod<SnmpConfig>
 */
final class SnmpPollingMethod extends PollingMethod
{
    public function __construct(
        private readonly ?SnmpBackendInterface $backend = null,
    ) {
    }

    public function definition(): SnmpDefinition
    {
        return new SnmpDefinition;
    }

    public function defaultAffectsAvailability(): bool
    {
        return true;
    }

    /**
     * @return array{transport: string, port: int, timeout: int|float, retries: int, max_repeaters: int, max_oid: int, bulk: bool, context: ?string, port_association_mode: string}
     */
    public function defaults(?Device $device = null): array
    {
        $os = $device?->os ?: 'generic';

        return [
            'transport' => LibrenmsConfig::get('snmp.transports.0', 'udp'),
            'port' => (int) LibrenmsConfig::get('snmp.port', 161),
            'timeout' => (float) LibrenmsConfig::get('snmp.timeout', 1),
            'retries' => (int) LibrenmsConfig::get('snmp.retries', 5),
            'max_repeaters' => max(0, (int) LibrenmsConfig::getOsSetting($os, 'snmp.max_repeaters', LibrenmsConfig::get('snmp.max_repeaters', 0))),
            'max_oid' => max(1, (int) LibrenmsConfig::getOsSetting($os, 'snmp_max_oid', LibrenmsConfig::get('snmp.max_oid', 10))),
            'bulk' => filter_var(LibrenmsConfig::getOsSetting($os, 'snmp_bulk', LibrenmsConfig::get('snmp_bulk', true)), FILTER_VALIDATE_BOOLEAN),
            'context' => null,
            'port_association_mode' => LibrenmsConfig::get('default_port_association_mode', 'ifIndex'),
        ];
    }

    public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): SnmpConfig
    {
        if ($deviceMethod === null && $device->pollingMethods->isEmpty()) {
            if ($device->exists) {
                Eventlog::log('Missing SNMP polling method, falling back to legacy device fields.', $device, 'snmp', Severity::Error);
            }

            return $this->legacyConfig($device);
        }

        return SnmpConfig::make(
            ($deviceMethod->settings ?? []) + $this->defaults($device),
            SnmpSecretData::fromArray($deviceMethod->secret->data ?? []),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        assert($config instanceof SnmpConfig);

        $backend = $this->backend ?? resolve(SnmpBackendInterface::class);

        $response = $backend->get(
            $device->pollerTarget(),
            ['SNMPv2-MIB::sysObjectID.0'],
            $config,
            SnmpQueryOptions::quickPrint()
        );

        $success = $response->getExitCode() === 0
            || $response->getExitCode() === 2
            || $response->isValid();

        $error = $success ? null : ($response->getErrorMessage() ?: ($response->stderr ?: null));

        return new ProbeResult($success, ['response' => $response], $error);
    }

    public function secretType(): SecretType
    {
        return SecretType::Snmp;
    }

    /**
     * @inheritDoc
     */
    public function discover(Device $device, DevicePollingMethod $deviceMethod): ProbeResult
    {
        // If specific credentials were supplied on the method, test them directly
        if ($this->hasCredentials($deviceMethod)) {
            $result = $this->probe($device, $this->config($device, $deviceMethod));
            if ($result->isSuccess()) {
                return $result;
            }

            return ProbeResult::failure($result->stats(), $result->errorMessage(), [$this->noReplyReason($deviceMethod->secret)]);
        }

        // Otherwise, attempt the default credentials in order
        $reasons = [];
        $lastResult = null;
        foreach ($this->defaultSecretsFor($deviceMethod) as $secret) {
            $deviceMethod->setRelation('secret', $secret);
            $deviceMethod->secret_id = $secret->id;

            $result = $this->probe($device, $this->config($device, $deviceMethod));
            if ($result->isSuccess()) {
                return $result;
            }

            $lastResult = $result;
            $reasons[] = $this->noReplyReason($secret);
        }

        return ProbeResult::failure($lastResult?->stats() ?? [], $lastResult?->errorMessage(), $reasons);
    }

    /**
     * Without credentials, use the first default credential.
     */
    public function assignDefaultSecret(DevicePollingMethod $deviceMethod): void
    {
        if ($this->hasCredentials($deviceMethod)) {
            return;
        }

        $secret = $this->defaultSecretsFor($deviceMethod)->first() ?? throw new MissingSecretException(PollingMethodType::Snmp);
        $deviceMethod->secret()->associate($secret);
    }

    private function hasCredentials(DevicePollingMethod $deviceMethod): bool
    {
        return $deviceMethod->secret !== null && SnmpSecretData::fromArray($deviceMethod->secret->data ?? [])->hasCredentials();
    }

    /**
     * All default credentials, or only those for the version when the secret has just a version.
     *
     * @return Collection<int, Secret>
     */
    private function defaultSecretsFor(DevicePollingMethod $deviceMethod): Collection
    {
        $version = $deviceMethod->secret ? SnmpSecretData::fromArray($deviceMethod->secret->data ?? [])->version : null;

        return $this->defaultSecrets($version);
    }

    /**
     * The default credentials in the order they should be tried, optionally only those for one SNMP version.
     *
     * @return Collection<int, Secret>
     */
    private function defaultSecrets(?string $version = null): Collection
    {
        /** @var array<int, int> $defaultSecretIds */
        $defaultSecretIds = (array) LibrenmsConfig::get('snmp.default_credentials', []);

        return Secret::where('secret_type', SecretType::Snmp)
            ->whereIn('id', $defaultSecretIds)
            ->get()
            ->sortBy(fn (Secret $secret) => array_search($secret->id, $defaultSecretIds))
            ->when($version !== null, fn (Collection $secrets) => $secrets->filter(
                fn (Secret $secret): bool => SnmpSecretData::fromArray($secret->data ?? [])->version === $version
            ))
            ->values();
    }

    private function noReplyReason(Secret $secret): string
    {
        return trans('exceptions.host_unreachable.no_reply_secret', [
            'version' => SnmpSecretData::fromArray($secret->data ?? [])->version,
            'secret' => $secret->description,
        ]);
    }

    public function enrichDeviceMetadata(Device $device): void
    {
        $sysName = SnmpQuery::device($device)->get('SNMPv2-MIB::sysName.0')->value();
        if (! empty($sysName)) {
            $device->sysName = (string) $sysName;
        }

        $device->os = Core::detectOS($device);
    }

    /**
     * Create from legacy device fields. Emergency fallback, do not use.
     */
    private function legacyConfig(Device $device): SnmpConfig
    {
        return $this->legacy(
            settings: [
                'transport' => $device->getAttribute('transport'),
                'port' => $device->getAttribute('port'),
                'timeout' => $device->getAttribute('timeout'),
                'retries' => $device->getAttribute('retries'),
                'max_repeaters' => $device->getAttrib('snmp_max_repeaters') ?: null, // legacy treated 0 as unset
                'max_oid' => $device->getAttrib('snmp_max_oid') ?: null,
                'bulk' => $device->getAttrib('snmp_bulk'),
                'port_association_mode' => $device->getAttribute('port_association_mode') !== null ? PortAssociationMode::getName((int) $device->getAttribute('port_association_mode')) : null,
            ],
            secretData: new SnmpSecretData(
                version: (string) ($device->getAttribute('snmpver') ?: 'v2c'),
                community: $device->getAttribute('community'),
                authlevel: $device->getAttribute('authlevel'),
                authname: $device->getAttribute('authname'),
                authpass: $device->getAttribute('authpass'),
                authalgo: $device->getAttribute('authalgo'),
                cryptoalgo: $device->getAttribute('cryptoalgo'),
                cryptopass: $device->getAttribute('cryptopass'),
            ),
            device: $device,
        );
    }

    /**
     * Config for a legacy device array.
     *
     * @param  array<string, mixed>|null  $device
     */
    public function configFromDeviceArray(?array $device): SnmpConfig
    {
        $device ??= [];
        $device_id = $device['device_id'] ?? 0;

        if (DeviceCache::has($device_id)) {
            return DeviceCache::get($device_id)->polling()->snmp();
        }

        return $this->legacy(
            settings: [
                'transport' => $device['transport'] ?? null,
                'port' => $device['port'] ?? null,
                'timeout' => $device['timeout'] ?? null,
                'retries' => $device['retries'] ?? null,
                'max_repeaters' => ($device['snmp_max_repeaters'] ?? null) ?: null, // legacy treated 0 as unset
                'max_oid' => ($device['snmp_max_oid'] ?? null) ?: null,
                'bulk' => $device['snmp_bulk'] ?? null,
                'port_association_mode' => isset($device['port_association_mode'])
                    ? (is_numeric($device['port_association_mode']) ? PortAssociationMode::getName((int) $device['port_association_mode']) : $device['port_association_mode'])
                    : null,
            ],
            secretData: new SnmpSecretData(
                version: (string) ($device['snmpver'] ?? 'v2c'),
                community: isset($device['community']) ? (string) $device['community'] : null,
                authlevel: $device['authlevel'] ?? null,
                authname: $device['authname'] ?? null,
                authpass: $device['authpass'] ?? null,
                authalgo: $device['authalgo'] ?? null,
                cryptoalgo: $device['cryptoalgo'] ?? null,
                cryptopass: $device['cryptopass'] ?? null,
            ),
            device: new Device(['os' => $device['os'] ?? null]),
        );
    }

    /**
     * @param  array<string, mixed>  $settings  uncast settings
     */
    private function legacy(array $settings, SnmpSecretData $secretData, Device $device): SnmpConfig
    {
        return SnmpConfig::make($this->definition()->filterOverrides($settings) + $this->defaults($device), $secretData);
    }
}
