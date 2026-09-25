<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\PollingMethodRegistry;

readonly class BuildDefaultPollingMethods
{
    public function __construct(
        private PollingMethodRegistry $pollingMethods,
    ) {
    }

    /**
     * Build a single candidate or transient polling method with its secret relation.
     *
     * @param  array<string, mixed>  $data
     */
    public function buildMethod(Device $device, PollingMethodType $type, array $data = []): ?DevicePollingMethod
    {
        $method = $this->pollingMethods->get($type);
        if (! $method) {
            return null;
        }

        $affectsAvailability = isset($data['affects_availability']) ? (bool) $data['affects_availability'] : null;
        $secret = $this->resolveSecret($device, $type, $data, $method->secretType());

        $pollingMethod = new DevicePollingMethod([
            'method_type' => $type,
            'enabled' => (bool) ($data['enabled'] ?? true),
            'affects_availability' => $affectsAvailability ?? $method->defaultAffectsAvailability(),
            'settings' => $method->filterOverrides($data['settings'] ?? [], $data['existing_settings'] ?? []),
        ]);
        $pollingMethod->setRelation('device', $device);

        if ($secret !== null) {
            $pollingMethod->setRelation('secret', $secret);
            if ($secret->exists) {
                $pollingMethod->secret_id = $secret->id;
            }
        }

        return $pollingMethod;
    }

    /**
     * Resolve the secret to attach: an explicitly given one, an existing one picked
     * by ID, a freshly-built one from posted secret data, or none if there's nothing
     * to attach.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveSecret(Device $device, PollingMethodType $type, array $data, mixed $secretType): ?Secret
    {
        if (isset($data['secret']) || $secretType === null) {
            return $data['secret'] ?? null;
        }

        $credentialMode = $data['credential_mode'] ?? 'default';
        $secretId = isset($data['secret_id']) && $data['secret_id'] !== '' ? (int) $data['secret_id'] : null;

        if ($credentialMode === 'existing' && $secretId !== null) {
            return Secret::resolveForType($secretId, $secretType);
        }

        if (empty($data['secret_data'])) {
            return null;
        }

        $description = ($credentialMode === 'new' && ! empty($data['description']))
            ? $data['description']
            : strtoupper($type->value) . ' ' . $device->hostname;

        return new Secret([
            'description' => $description,
            'secret_type' => $secretType,
            'data' => $data['secret_data'],
        ]);
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

            $pollingMethod = $this->buildMethod($device, $type, $data);
            if ($pollingMethod !== null) {
                $pollingMethods->push($pollingMethod);
            }
        }

        return $pollingMethods;
    }
}
