<?php

namespace App\Actions\Device;

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
     */
    public function buildMethod(Device $device, PollingMethodType $type, array $data = []): DevicePollingMethod
    {
        $method = $this->pollingMethods->get($type);
        $pollingMethod = new DevicePollingMethod([
            'method_type' => $type,
            'enabled' => (bool) ($data['enabled'] ?? true),
            'affects_availability' => (bool) ($data['affects_availability'] ?? $method->defaultConfig()->affectsAvailability),
            'settings' => $method->definition()->filterOverrides($data['settings'] ?? []),
        ]);
        $pollingMethod->setRelation('device', $device);

        $secret = $this->resolveSecret->execute($device, $type, $data);
        if ($secret !== null) {
            $pollingMethod->setRelation('secret', $secret);
            if ($secret->exists) {
                $pollingMethod->secret_id = $secret->id;
            }
        }

        return $pollingMethod;
    }

    /**
     * Build default polling methods collection for a new device.
     *
     * @param  array<string, mixed>  $input
     * @return Collection<int, DevicePollingMethod>
     */
    public function execute(Device $device, array $input = []): Collection
    {
        $pollingMethods = collect();

        $methodsData = $input['methods'] ?? [
            'icmp' => ['active' => true],
            'snmp' => ['active' => true],
        ];

        foreach ($methodsData as $methodName => $data) {
            if (empty($data['active'])) {
                continue;
            }

            $type = PollingMethodType::tryFrom($methodName);
            if ($type !== null) {
                $pollingMethods->push($this->buildMethod($device, $type, $data));
            }
        }

        return $pollingMethods;
    }
}
