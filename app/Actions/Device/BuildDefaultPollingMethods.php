<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;

class BuildDefaultPollingMethods
{
    public function __construct(
        private readonly \LibreNMS\Polling\Method\PollingMethodRegistry $registry,
    ) {
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
            'icmp' => [
                'active' => true,
                'affects_availability' => true,
            ],
            'snmp' => [
                'active' => true,
                'affects_availability' => true,
                'credential_mode' => 'default',
            ],
        ];

        foreach ($methodsData as $methodName => $data) {
            if (empty($data['active'])) {
                continue;
            }

            $type = PollingMethodType::tryFrom($methodName);
            if (! $type) {
                continue;
            }

            $settings = $data['settings'] ?? [];
            $credentialMode = $data['credential_mode'] ?? 'default';
            $secretId = isset($data['secret_id']) ? (int) $data['secret_id'] : null;
            $affectsAvailability = isset($data['affects_availability']) ? (bool) $data['affects_availability'] : null;

            $secret = null;
            $secretData = null;
            if ($credentialMode === 'existing' && $secretId !== null) {
                $secret = Secret::resolveForType($secretId, $type);
            } elseif (! empty($data['secret_data'])) {
                $secretType = SecretType::tryFrom($type->value);
                $secretData = $secretType?->createData($data['secret_data']);
            }

            $pollingMethodRegistry = $this->registry;
            $definition = $pollingMethodRegistry->definition($type);

            $pollingMethod = new DevicePollingMethod([
                'method_type' => $type,
                'enabled' => true,
                'affects_availability' => $affectsAvailability ?? $this->registry->defaultAffectsAvailability($type),
                'settings' => $definition ? $definition->filterOverrides($settings) : $settings,
            ]);
            $pollingMethod->setRelation('device', $device);

            if ($secret !== null) {
                $pollingMethod->setRelation('secret', $secret);
                $pollingMethod->secret_id = $secret->id;
            } elseif ($secretData !== null) {
                $createdSecret = new Secret([
                    'secret_type' => $type->value,
                    'description' => ($credentialMode === 'new' && ! empty($data['description'])) ? $data['description'] : (strtoupper($type->value) . ' ' . $device->hostname),
                    'data' => $secretData->toArray(),
                ]);
                $pollingMethod->setRelation('secret', $createdSecret);
            }

            $pollingMethods->push($pollingMethod);
        }

        return $pollingMethods;
    }
}
