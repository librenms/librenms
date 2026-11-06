<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\PollingMethodRegistry;

readonly class BuildDefaultPollingMethods
{
    public function __construct(
        private PollingMethodRegistry $pollingMethods,
        private ResolvePollingMethodSecret $resolveSecret,
    ) {
    }

    /**
     * Build a single candidate or transient polling method with its secret relation.
     *
     * @param  array<string, mixed>  $data
     * @param  string  $errorKeyPrefix  prefix for the validation error keys, where the data is nested in the request
     */
    public function buildMethod(Device $device, PollingMethodType $type, array $data = [], string $errorKeyPrefix = ''): DevicePollingMethod
    {
        $method = $this->pollingMethods->get($type);
        $pollingMethod = new DevicePollingMethod([
            'method_type' => $type,
            'enabled' => (bool) ($data['enabled'] ?? true),
            'affects_availability' => (bool) ($data['affects_availability'] ?? $method->defaultAffectsAvailability()),
            'settings' => $method->definition()->filterOverrides($data['settings'] ?? []),
        ]);
        $pollingMethod->setRelation('device', $device);

        $secret = $this->resolveSecret->execute($device, $type, $data, $errorKeyPrefix);
        if ($secret !== null) {
            $pollingMethod->setRelation('secret', $secret);
            if ($secret->exists) {
                $pollingMethod->secret_id = $secret->id;
            }
        } elseif (isset($data['secret_type'])) {
            // without a secret, a type limits the default credentials that are tried, like only v3 credentials
            $pollingMethod->defaultSecretType = $method->secretTypeFrom($data['secret_type']);
        }

        return $pollingMethod;
    }

    /**
     * Build default polling methods collection for a new device.
     *
     * @param  array<string, mixed>  $input
     * @param  string  $errorKeyPrefix  prefix for the validation error keys of the methods, where they are nested in the request
     * @return Collection<int, DevicePollingMethod>
     */
    public function execute(Device $device, array $input = [], string $errorKeyPrefix = ''): Collection
    {
        $pollingMethods = collect();

        $methodsData = $input['methods'] ?? [
            'icmp' => ['active' => (bool) LibrenmsConfig::get('icmp_check', true)],
            'snmp' => ['active' => true],
        ];

        foreach ($methodsData as $methodName => $data) {
            if (empty($data['active'])) {
                continue;
            }

            $type = PollingMethodType::tryFromKnown($methodName);
            if ($type !== null) {
                $pollingMethods->push($this->buildMethod($device, $type, $data, $errorKeyPrefix === '' ? '' : "$errorKeyPrefix$methodName."));
            }
        }

        return $pollingMethods;
    }
}
