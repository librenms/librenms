<?php

/**
 * SnmpConfig.php
 *
 * Value object holding SNMP connection settings and credentials for a target.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling\Method\Config;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Util\IP;

class SnmpConfig extends PollingMethodConfig
{
    public function __construct(
        public bool $enabled = true,
        public bool $affectsAvailability = true,

        // Secrets
        public string $version = 'v2c',
        public ?string $community = null,
        public ?string $authname = null,
        public ?string $authpass = null,
        public ?string $authlevel = null,
        public ?string $authalgo = null,
        public ?string $cryptopass = null,
        public ?string $cryptoalgo = null,

        // Settings
        public string $transport = 'udp',
        public int $port = 161,
        public ?string $context = null,
        public int|float $timeout = 1,
        public int $retries = 5,
        public int $maxRepeaters = 0,
        public int $maxOid = 10,
        public bool $bulk = true,
    ) {
    }

    public static function fromDevice(Device $device): static
    {
        $method = $device->pollingMethod(PollingMethodType::Snmp);
        if ($method) {
            return static::fromModel($method);
        }

        $os = $device->os ?? 'generic';
        $timeout = (float) ($device->timeout > 0 ? $device->timeout : LibrenmsConfig::get('snmp.timeout', 1));
        $retries = (int) (is_numeric($device->retries) ? $device->retries : LibrenmsConfig::get('snmp.retries', 5));
        $maxRepeaters = (int) ($device->getAttrib('snmp_max_repeaters') ?: LibrenmsConfig::getOsSetting($os, 'snmp.max_repeaters', LibrenmsConfig::get('snmp.max_repeaters', 0)));
        $configuredMaxOid = (int) ($device->getAttrib('snmp_max_oid') ?: LibrenmsConfig::getOsSetting($os, 'snmp_max_oid', LibrenmsConfig::get('snmp.max_oid', 10)));
        $rawBulk = LibrenmsConfig::getOsSetting($os, 'snmp_bulk', LibrenmsConfig::get('snmp_bulk', true));

        return new static(
            enabled: ! (bool) ($device->snmp_disable ?? false),
            affectsAvailability: true,
            version: (string) ($device->getAttribute('snmpver') ?: ($device->getAttribute('version') ?: 'v2c')),
            community: $device->getAttribute('community') ?: 'public',
            authname: $device->getAttribute('authname'),
            authpass: $device->getAttribute('authpass'),
            authlevel: $device->getAttribute('authlevel'),
            authalgo: $device->getAttribute('authalgo'),
            cryptopass: $device->getAttribute('cryptopass'),
            cryptoalgo: $device->getAttribute('cryptoalgo'),
            transport: (string) ($device->transport ?: 'udp'),
            port: (int) ($device->port ?: 161),
            context: null,
            timeout: max(0.1, $timeout),
            retries: max(0, $retries),
            maxRepeaters: max(0, $maxRepeaters),
            maxOid: max(1, $configuredMaxOid),
            bulk: filter_var($rawBulk, FILTER_VALIDATE_BOOLEAN),
        );
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public static function fromModel(DevicePollingMethod $method): static
    {
        $definition = PollingMethodType::Snmp->definition();
        $secretDefinition = $definition->secretDefinition();

        $settings = $definition->resolveValues($method->settings ?? []);
        $resolvedData = $secretDefinition ? $secretDefinition->resolveValues($method->secret?->data ?? []) : [];
        $secretData = \LibreNMS\Polling\Secrets\Data\SnmpSecretData::fromArray($resolvedData);

        $os = $method->device?->os ?? 'generic';

        $timeout = (float) ($settings['timeout'] > 0 ? $settings['timeout'] : LibrenmsConfig::get('snmp.timeout', 1));
        $retries = (int) (is_numeric($settings['retries']) ? $settings['retries'] : LibrenmsConfig::get('snmp.retries', 5));
        $maxRepeaters = (int) ($settings['max_repeaters'] ?: LibrenmsConfig::getOsSetting($os, 'snmp.max_repeaters', LibrenmsConfig::get('snmp.max_repeaters', 0)));
        $configuredMaxOid = (int) ($settings['max_oid'] ?: LibrenmsConfig::getOsSetting($os, 'snmp_max_oid', LibrenmsConfig::get('snmp.max_oid', 10)));
        $rawBulk = LibrenmsConfig::getOsSetting($os, 'snmp_bulk', LibrenmsConfig::get('snmp_bulk', true));

        return new static(
            enabled: $method->enabled,
            affectsAvailability: $method->affects_availability,
            version: $secretData->version,
            community: $secretData->community,
            authname: $secretData->authname,
            authpass: $secretData->authpass,
            authlevel: $secretData->authlevel,
            authalgo: $secretData->authalgo,
            cryptopass: $secretData->cryptopass,
            cryptoalgo: $secretData->cryptoalgo,
            transport: $settings['transport'],
            port: (int) ($settings['port'] ?? 161),
            context: $secretData->context,
            timeout: max(0.1, $timeout),
            retries: max(0, $retries),
            maxRepeaters: max(0, $maxRepeaters),
            maxOid: max(1, $configuredMaxOid),
            bulk: filter_var($rawBulk, FILTER_VALIDATE_BOOLEAN),
        );
    }

    public static function fromDeviceArray(?array $device): self
    {
        if (isset($device['ip']) && ! IP::isValid($device['ip'])) {
            $device['ip'] = @inet_ntop($device['ip']) ?: null;
        }

        $device ??= [];

        if (! empty($device['device_id'])) {
            $deviceModel = \App\Facades\DeviceCache::get((int) $device['device_id']);
            if ($deviceModel && $deviceModel->exists && $deviceModel->pollingMethod(PollingMethodType::Snmp)) {
                return static::fromDevice($deviceModel);
            }
        }

        $os = $device['os'] ?? 'generic';
        $timeout = (float) ((isset($device['timeout']) && $device['timeout'] > 0) ? $device['timeout'] : LibrenmsConfig::get('snmp.timeout', 1));
        $retries = (int) (isset($device['retries']) && is_numeric($device['retries']) ? $device['retries'] : LibrenmsConfig::get('snmp.retries', 5));
        $maxRepeaters = (int) (($device['snmp_max_repeaters'] ?? null) ?: LibrenmsConfig::getOsSetting($os, 'snmp.max_repeaters', LibrenmsConfig::get('snmp.max_repeaters', 0)));
        $configuredMaxOid = (int) (($device['snmp_max_oid'] ?? null) ?: LibrenmsConfig::getOsSetting($os, 'snmp_max_oid', LibrenmsConfig::get('snmp.max_oid', 10)));
        $rawBulk = LibrenmsConfig::getOsSetting($os, 'snmp_bulk', LibrenmsConfig::get('snmp_bulk', true));

        return new static(
            enabled: ! (bool) ($device['snmp_disable'] ?? false),
            affectsAvailability: true,
            version: (string) ($device['snmpver'] ?? $device['version'] ?? 'v2c'),
            community: $device['community'] ?? 'public',
            authname: $device['authname'] ?? null,
            authpass: $device['authpass'] ?? null,
            authlevel: $device['authlevel'] ?? null,
            authalgo: $device['authalgo'] ?? null,
            cryptopass: $device['cryptopass'] ?? null,
            cryptoalgo: $device['cryptoalgo'] ?? null,
            transport: (string) ($device['transport'] ?? 'udp'),
            port: (int) ($device['port'] ?? 161),
            context: $device['context_name'] ?? null,
            timeout: max(0.1, $timeout),
            retries: max(0, $retries),
            maxRepeaters: max(0, $maxRepeaters),
            maxOid: max(1, $configuredMaxOid),
            bulk: filter_var($rawBulk, FILTER_VALIDATE_BOOLEAN),
        );
    }
}
