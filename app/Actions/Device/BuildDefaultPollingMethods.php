<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;

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
                $secretData = $data['secret_data'] ?? [];
                $credentialMode = $data['credential_mode'] ?? 'default';
                $secretId = isset($data['secret_id']) ? (int) $data['secret_id'] : null;
                $affectsAvailability = isset($data['affects_availability']) ? (bool) $data['affects_availability'] : null;

                $pollingMethod = DevicePollingMethod::transient(
                    type: $type,
                    settings: $settings,
                    secretData: ($credentialMode === 'existing' || empty($secretData)) ? [] : $secretData,
                    device: $device,
                    affectsAvailability: $affectsAvailability,
                );

                if ($credentialMode === 'existing' && $secretId !== null) {
                    $secret = Secret::resolveForType($secretId, $type);
                    $pollingMethod->setRelation('secret', $secret);
                    $pollingMethod->secret_id = $secret->id;
                } elseif ($credentialMode === 'new' && ! empty($secretData) && ! empty($data['description'])) {
                    if ($pollingMethod->secret) {
                        $pollingMethod->secret->description = $data['description'];
                        $pollingMethod->secret->default = (bool) ($data['default'] ?? false);
                    }
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

            $snmpData = [];
            if ($snmpver || $community || $auth || $priv || $authname || isset($input['authlevel'])) {
                $snmpData = [
                    'version' => $snmpver ?: 'v2c',
                    'community' => $community,
                    'authlevel' => $authlevel ?: 'noAuthNoPriv',
                    'authname' => $authname ?: 'root',
                    'authpass' => $auth,
                    'authalgo' => $authalgo ?: 'MD5',
                    'cryptopass' => $priv,
                    'cryptoalgo' => $cryptoalgo ?: 'AES',
                ];
            }

            $pollingMethods->push(DevicePollingMethod::transient(
                PollingMethodType::Snmp,
                settings: $settings,
                secretData: $snmpData,
                device: $device,
                affectsAvailability: true,
            ));
        }

        return $pollingMethods;
    }
}
