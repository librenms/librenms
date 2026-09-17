<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;

class BuildDefaultPollingMethods
{
    /**
     * Build default polling methods collection for a new device.
     *
     * @param  array<string, mixed>  $input
     * @return Collection<int, DevicePollingMethod>
     */
    public function execute(Device $device, array $input): Collection
    {
        $pollingMethods = collect();

        if (isset($input['methods']) && is_array($input['methods'])) {
            foreach ($input['methods'] as $methodName => $data) {
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

                $pollingMethod = DevicePollingMethod::transient(
                    type: $type,
                    settings: $settings,
                    secretData: $secretData,
                    device: $device,
                    affectsAvailability: $affectsAvailability,
                    secret: $secret,
                );

                if ($credentialMode === 'new' && $pollingMethod->secret && ! empty($data['description'])) {
                    $pollingMethod->secret->description = $data['description'];
                }

                $pollingMethods->push($pollingMethod);
            }

            return $pollingMethods;
        }

        // ICMP polling method is always added
        $pollingMethods->push(DevicePollingMethod::transient(
            PollingMethodType::Icmp,
            device: $device,
            affectsAvailability: false,
        ));

        $snmpDisabled = ! empty($input['ping_only']) || ! empty($input['snmp_disable']);

        if (! $snmpDisabled) {
            $settings = array_filter([
                'port' => $input['port'] ?? null,
                'transport' => $input['transport'] ?? null,
            ], fn ($v) => $v !== null);

            $snmpver = $input['snmpver'] ?? (isset($input['v3']) && $input['v3'] ? 'v3' : (isset($input['v2c']) && $input['v2c'] ? 'v2c' : (isset($input['v1']) && $input['v1'] ? 'v1' : ($input['version'] ?? ''))));
            $community = $input['community'] ?? null;
            $auth = $input['authpass'] ?? $input['auth'] ?? null;
            $priv = $input['cryptopass'] ?? $input['priv'] ?? null;
            $authlevel = $input['authlevel'] ?? ($auth ? 'auth' : 'noAuth') . (($priv && $auth) ? 'Priv' : 'NoPriv');
            $authname = $input['authname'] ?? $input['security-name'] ?? $input['security_name'] ?? null;
            $authalgo = $input['authalgo'] ?? $input['auth-protocol'] ?? $input['auth_protocol'] ?? null;
            $cryptoalgo = $input['cryptoalgo'] ?? $input['privacy-protocol'] ?? $input['privacy_protocol'] ?? null;

            $secretData = null;
            if ($snmpver || $community || $auth || $priv || $authname || isset($input['authlevel'])) {
                $secretData = new SnmpSecretData(
                    version: $snmpver ?: 'v2c',
                    community: $community,
                    authlevel: $authlevel ?: 'noAuthNoPriv',
                    authname: $authname ?: 'root',
                    authpass: $auth,
                    authalgo: $authalgo ?: 'MD5',
                    cryptoalgo: $cryptoalgo ?: 'AES',
                    cryptopass: $priv,
                );
            }

            $pollingMethods->push(DevicePollingMethod::transient(
                type: PollingMethodType::Snmp,
                settings: $settings,
                secretData: $secretData,
                device: $device,
                affectsAvailability: true,
            ));
        }

        return $pollingMethods;
    }
}
