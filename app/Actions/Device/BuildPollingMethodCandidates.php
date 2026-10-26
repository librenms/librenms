<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\PollingMethodRegistry;

readonly class BuildPollingMethodCandidates
{
    public function __construct(
        private PollingMethodRegistry $pollingMethods,
    ) {
    }

    /**
     * The ways to try a polling method, most preferred first. Copies, the method is not changed.
     * An SNMP method tries its secret, or the default credentials without one. For a new device without an SNMP version,
     * each community is tried again as v1 after the others, like the legacy default versions v2c, v3, v1.
     * Other methods are tried as they are.
     * Empty when an SNMP method has no secret and there are no default credentials.
     *
     * @return list<DevicePollingMethod>
     */
    public function execute(Device $device, DevicePollingMethod $deviceMethod): array
    {
        if ($deviceMethod->method_type !== PollingMethodType::Snmp) {
            return [$deviceMethod->replicate()];
        }

        $settings = $deviceMethod->settings ?? [];
        $withV1 = ! $device->exists && empty($settings['version']);
        $secrets = $deviceMethod->secret !== null ? [$deviceMethod->secret] : $this->defaultSnmpSecrets($deviceMethod->defaultSecretType);

        $candidates = [];
        $v1Candidates = [];
        foreach ($secrets as $secret) {
            $candidates[] = $this->withSecret($deviceMethod, $secret);

            if ($withV1 && $secret->secret_type === SecretType::SnmpCommunity) {
                $v1Candidates[] = $this->withSecret($deviceMethod, $secret, [...$settings, 'version' => 'v1']);
            }
        }

        return [...$candidates, ...$v1Candidates];
    }

    /**
     * The default SNMP credentials in the order they are tried.
     *
     * @param  SecretType|null  $secretType  only the credentials of this type, null for all
     * @return Collection<int, Secret>
     */
    private function defaultSnmpSecrets(?SecretType $secretType): Collection
    {
        /** @var array<int, int> $defaultSecretIds */
        $defaultSecretIds = (array) LibrenmsConfig::get('snmp.default_credentials', []);

        return Secret::whereIn('secret_type', $secretType ? [$secretType] : $this->pollingMethods->get(PollingMethodType::Snmp)->secretTypes())
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
