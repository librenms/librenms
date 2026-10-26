<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\MissingSecretException;
use LibreNMS\Modules\Core;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Definitions\SnmpDefinition;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\Secrets\Data\SnmpCommunitySecretData;
use LibreNMS\Polling\Secrets\Data\SnmpV3SecretData;
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
     * @return array{version: string, transport: string, port: int, timeout: int|float, retries: int, max_repeaters: int, max_oid: int, bulk: bool, context: ?string, port_association_mode: string}
     */
    public function defaults(?Device $device = null): array
    {
        $os = $device?->os ?: 'generic';

        return [
            'version' => 'v2c',
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
        return SnmpConfig::make(
            ($deviceMethod->settings ?? []) + $this->defaults($device),
            $this->secretData($deviceMethod?->secret),
        );
    }

    /**
     * The SNMP version of the method, without decrypting the secret.
     */
    public function version(?DevicePollingMethod $deviceMethod): string
    {
        return SnmpConfig::version($deviceMethod?->secret?->secret_type, $deviceMethod?->settings['version'] ?? null);
    }

    private function secretData(?Secret $secret): SnmpCommunitySecretData|SnmpV3SecretData|null
    {
        $data = $secret?->secret_type->data($secret->data);

        return $data instanceof SnmpCommunitySecretData || $data instanceof SnmpV3SecretData ? $data : null;
    }

    /**
     * The transport for the device, without decrypting the secret.
     */
    public function transport(Device $device): string
    {
        return $device->pollingMethod(PollingMethodType::Snmp)?->settings['transport'] ?? $this->defaults($device)['transport'];
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

    /**
     * A community for v1 and v2c, or a v3 secret.
     */
    public function secretTypes(): array
    {
        return [SecretType::SnmpCommunity, SecretType::SnmpV3];
    }

    /**
     * @inheritDoc
     */
    public function discover(Device $device, DevicePollingMethod $deviceMethod): ProbeResult
    {
        // If specific credentials were supplied on the method, test them directly
        if ($deviceMethod->secret !== null) {
            $result = $this->probe($device, $this->config($device, $deviceMethod));

            return $result->isSuccess() ? $result : ProbeResult::failure($result->stats(), $result->errorMessage(), [$this->noReplyReason($deviceMethod, $deviceMethod->secret)]);
        }

        // Otherwise, attempt the default credentials in order
        $reasons = [];
        $lastResult = null;
        foreach ($this->defaultSecrets($deviceMethod->defaultSecretTypes) as $secret) {
            $deviceMethod->setRelation('secret', $secret);
            $deviceMethod->secret_id = $secret->id;

            $result = $this->probe($device, $this->config($device, $deviceMethod));
            if ($result->isSuccess()) {
                return $result;
            }

            $lastResult = $result;
            $reasons[] = $this->noReplyReason($deviceMethod, $secret);
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

        $secret = $this->defaultSecrets($deviceMethod->defaultSecretTypes)->first() ?? throw new MissingSecretException(PollingMethodType::Snmp);
        $deviceMethod->secret()->associate($secret);
    }

    /**
     * The default credentials in the order they should be tried.
     *
     * @param  SecretType[]|null  $secretTypes  only the credentials of these types, null for all
     * @return Collection<int, Secret>
     */
    public function defaultSecrets(?array $secretTypes = null): Collection
    {
        /** @var array<int, int> $defaultSecretIds */
        $defaultSecretIds = (array) LibrenmsConfig::get('snmp.default_credentials', []);

        return Secret::whereIn('secret_type', $secretTypes ?? $this->secretTypes())
            ->whereIn('id', $defaultSecretIds)
            ->get()
            ->sortBy(fn (Secret $secret) => array_search($secret->id, $defaultSecretIds))
            ->values();
    }

    private function noReplyReason(DevicePollingMethod $deviceMethod, Secret $secret): string
    {
        return trans('exceptions.host_unreachable.no_reply_secret', [
            'version' => $this->version($deviceMethod),
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
