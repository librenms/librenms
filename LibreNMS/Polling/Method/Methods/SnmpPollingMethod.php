<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Eventlog;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Enum\PollingMethodType;
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

    public function defaultConfig(?Device $device = null): SnmpConfig
    {
        return SnmpConfig::default($device?->os);
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

    public function fallbackConfig(Device $device): SnmpConfig
    {
        if ($device->relationLoaded('pollingMethods') && $device->pollingMethods->isNotEmpty()) {
            return parent::fallbackConfig($device);
        }

        if ($device->exists) {
            Eventlog::log('Missing SNMP polling method, falling back to legacy device fields.', $device, 'snmp', Severity::Error);
        }

        return SnmpConfig::fromLegacyDeviceFields($device);
    }

    /**
     * @inheritDoc
     */
    public function discover(Device $device, DevicePollingMethod $deviceMethod): ProbeResult
    {
        // If a specific secret was supplied on the method, test that directly
        if ($deviceMethod->relationLoaded('secret') && $deviceMethod->secret !== null) {
            $result = $this->probe($device, $this->config($deviceMethod));
            if ($result->isSuccess()) {
                return $result;
            }

            return ProbeResult::failure($result->stats(), $result->errorMessage(), [$this->noReplyReason($deviceMethod->secret)]);
        }

        // Otherwise, attempt ordered default credentials
        $defaultSecrets = $this->defaultSecrets();

        $reasons = [];
        $lastResult = null;
        foreach ($defaultSecrets as $secret) {
            $deviceMethod->setRelation('secret', $secret);
            $deviceMethod->secret_id = $secret->id;

            $result = $this->probe($device, $this->config($deviceMethod));
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
        if ($deviceMethod->secret !== null) {
            return;
        }

        $secret = $this->defaultSecrets()->first() ?? throw new MissingSecretException(PollingMethodType::Snmp);
        $deviceMethod->secret()->associate($secret);
    }

    /**
     * The default credentials in the order they should be tried.
     *
     * @return Collection<int, Secret>
     */
    private function defaultSecrets(): Collection
    {
        /** @var array<int, int> $defaultSecretIds */
        $defaultSecretIds = (array) LibrenmsConfig::get('snmp.default_credentials', []);

        return Secret::where('secret_type', SecretType::Snmp)
            ->whereIn('id', $defaultSecretIds)
            ->get()
            ->sortBy(fn (Secret $secret) => array_search($secret->id, $defaultSecretIds))
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
}
