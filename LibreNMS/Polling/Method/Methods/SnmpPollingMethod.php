<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Eventlog;
use App\Models\Secret;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Enum\SecretType;
use LibreNMS\Enum\Severity;
use LibreNMS\Modules\Core;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SnmpQuery;

final class SnmpPollingMethod extends PollingMethod
{
    public function __construct(
        private readonly ?SnmpBackendInterface $backend = null,
    ) {
    }

    public function defaultConfig(): SnmpConfig
    {
        return SnmpConfig::default();
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

    protected function configFromSettings(DevicePollingMethod $deviceMethod): SnmpConfig
    {
        return SnmpConfig::fromSettings(
            settings: $deviceMethod->settings ?? [],
            secretData: SnmpSecretData::fromArray($deviceMethod->secret->data ?? []),
            os: $deviceMethod->device?->os,
        );
    }

    public function fallbackConfig(Device $device): SnmpConfig
    {
        if ($device->relationLoaded('pollingMethods') && $device->pollingMethods->isNotEmpty()) {
            /** @var SnmpConfig */
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
        /** @var array<int, int> $defaultSecretIds */
        $defaultSecretIds = (array) LibrenmsConfig::get('snmp.default_credentials', []);
        $defaultSecrets = Secret::where('secret_type', SecretType::Snmp)
            ->whereIn('id', $defaultSecretIds)
            ->get()
            ->sortBy(fn ($s) => array_search($s->id, $defaultSecretIds));

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
