<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\PollingMethodRegistry;

readonly class BuildPollingMethodCandidates
{
    /**
     * The config of the default credentials of each polling method type, tried in order when a method has no secret.
     */
    private const DEFAULT_CREDENTIALS = [
        'snmp' => 'snmp.default_credentials',
    ];

    public function __construct(
        private PollingMethodRegistry $pollingMethods,
    ) {
    }

    /**
     * The ways to try a polling method, most preferred first. Copies, the method is not changed, see apply().
     * A method tries its secret, or without one its default credentials. A method without default credentials is tried as it is.
     * For a new device, each secret is then tried again with the retry settings of its type (a community as v1),
     * unless the method sets them. Retries come after every other candidate, like the legacy default SNMP versions v2c, v3, v1.
     * Empty when a method has no secret and none of its default credentials exist.
     *
     * @return list<DevicePollingMethod>
     */
    public function execute(Device $device, DevicePollingMethod $deviceMethod): array
    {
        $secrets = $deviceMethod->secret !== null ? [$deviceMethod->secret] : $this->defaultSecrets($deviceMethod);
        if ($secrets === null) {
            return [$deviceMethod->replicate()];
        }

        $settings = $deviceMethod->settings ?? [];
        $givenSettings = array_filter($settings);
        $candidates = [];
        $retries = [];
        foreach ($secrets as $secret) {
            $candidates[] = $this->withSecret($deviceMethod, $secret);

            foreach ($device->exists ? [] : $this->retrySettings($secret->secret_type) as $retry) {
                // a setting the method sets is not retried with another value
                if (array_intersect_key($givenSettings, $retry) === []) {
                    $retries[] = $this->withSecret($deviceMethod, $secret, [...$settings, ...$retry]);
                }
            }
        }

        return [...$candidates, ...$retries];
    }

    /**
     * Give the method the secret and settings of the candidate that worked, or that is used without a check.
     */
    public function apply(DevicePollingMethod $candidate, DevicePollingMethod $deviceMethod): void
    {
        // the candidate is a copy without the key and timestamps, the method keeps those
        $deviceMethod->setRawAttributes([...$deviceMethod->getAttributes(), ...$candidate->getAttributes()]);
        $deviceMethod->setRelations([...$deviceMethod->getRelations(), ...$candidate->getRelations()]);
    }

    /**
     * Settings to try a new device's secret of this type with again, when the other candidates did not work.
     *
     * @return list<array<string, mixed>>
     */
    private function retrySettings(SecretType $secretType): array
    {
        return match ($secretType) {
            SecretType::SnmpCommunity => [['version' => 'v1']], // a community is tried as v2c, some devices only speak v1
            default => [],
        };
    }

    /**
     * The default credentials in the order they are tried, null when the method has none.
     *
     * @return Collection<int, Secret>|null
     */
    private function defaultSecrets(DevicePollingMethod $deviceMethod): ?Collection
    {
        $config = self::DEFAULT_CREDENTIALS[$deviceMethod->method_type->value] ?? null;
        if ($config === null) {
            return null;
        }

        /** @var array<int, int> $defaultSecretIds */
        $defaultSecretIds = (array) LibrenmsConfig::get($config, []);
        $secretTypes = $deviceMethod->defaultSecretType ? [$deviceMethod->defaultSecretType] : $this->pollingMethods->get($deviceMethod->method_type)->secretTypes();

        return Secret::whereIn('secret_type', $secretTypes)
            ->whereIn('id', $defaultSecretIds)
            ->get()
            ->sortBy(fn (Secret $secret) => array_search($secret->id, $defaultSecretIds))
            ->values();
    }

    /**
     * @param  array<string, mixed>|null  $settings  null keeps the settings of the method
     */
    private function withSecret(DevicePollingMethod $deviceMethod, Secret $secret, ?array $settings = null): DevicePollingMethod
    {
        $candidate = $deviceMethod->replicate();
        if ($settings !== null) {
            $candidate->settings = $settings;
        }
        $candidate->secret_id = $secret->id;
        $candidate->setRelation('secret', $secret);

        return $candidate;
    }
}
