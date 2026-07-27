<?php

namespace LibreNMS\Polling\Method;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Interfaces\PollingMethodInterface;

readonly class PollingMethodRepository
{
    public function __construct(
        private Device $device,
    ) {
    }

    /**
     * Create or update a polling method row (and its associated Secret, if applicable).
     * Delegates all type-specific logic to the PollingMethod class for that type.
     *
     * @param  array<string, mixed>  $settings  Non-sensitive per-device settings (merged with existing).
     * @param  array<string, mixed>  $secretData  Credential fields (ignored for methods without a secret).
     */
    public function save(PollingMethodType $type, array $settings = [], array $secretData = [], bool $enabled = true, bool $affectsAvailability = false): DevicePollingMethod
    {
        $definition = PollingMethodDefinition::for($type);

        $method = DevicePollingMethod::with('secret')
            ->firstOrNew([
                'device_id' => $this->device->device_id,
                'method_type' => $type,
            ]);

        $method->enabled = $enabled;
        $method->affects_availability = $affectsAvailability;

        if (! empty($settings)) {
            $method->settings = array_merge($method->settings ?? [], $settings);
        }

        if (! empty($secretData) && $definition->secretClass()) {
            if ($method->secret) {
                $method->secret->update(['data' => array_merge($method->secret->data, $secretData)]);
            } else {
                $secretType = SecretType::fromClass($definition->secretClass());
                $secret = Secret::create([
                    'description' => $secretType->name . ' ' . $this->device->hostname,
                    'secret_type' => $secretType,
                    'default' => false,
                    'data' => $secretData,
                ]);
                $method->secret_id = $secret->id;
            }
        }

        $method->save();
        $this->device->load('pollingMethods');

        return $method;
    }

    /**
     * @template T of PollingMethodInterface
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function pollingMethod(PollingMethodType $type, string $class): PollingMethodInterface
    {
        return  $this->device->pollingMethods->firstWhere('method_type', $type)?->toPollingMethod() ?? $class::disabled();
    }

    public function snmp(): SnmpPollingMethod
    {
        return $this->pollingMethod(PollingMethodType::Snmp, SnmpPollingMethod::class);
    }

    public function icmp(): IcmpPollingMethod
    {
        return $this->pollingMethod(PollingMethodType::Icmp, IcmpPollingMethod::class);
    }

    public function ipmi(): IpmiPollingMethod
    {
        return $this->pollingMethod(PollingMethodType::Ipmi, IpmiPollingMethod::class);
    }

    public function unixAgent(): UnixAgentPollingMethod
    {
        return $this->pollingMethod(PollingMethodType::UnixAgent, UnixAgentPollingMethod::class);
    }
}
