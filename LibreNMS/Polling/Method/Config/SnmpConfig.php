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
use LibreNMS\Interfaces\PollingMethodConfigInterface;
use SnmpQuery;
use LibreNMS\Util\Rewrite;

readonly class SnmpConfig implements PollingMethodConfigInterface
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
        public ?string $context = null,

        // Settings
        public string $transport = 'udp',
        public int $port = 161,
        public int|float $timeout = 1,
        public int $retries = 5,
        public int $maxRepeaters = 0,
        public int $maxOid = 10,
        public bool $bulk = true,
    ) {
    }

    public static function fromDevice(Device $device): static
    {
        return static::fromModel($device->pollingMethod(PollingMethodType::Snmp));
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
        $secretData = $secretDefinition->resolveValues($method->secret->data ?? []);

        $os = $method->device->os ?? 'generic';

        $timeout = (float) ($settings['timeout'] > 0 ? $settings['timeout'] : LibrenmsConfig::get('snmp.timeout', 1));
        $retries = (int) (is_numeric($settings['retries']) ? $settings['retries'] : LibrenmsConfig::get('snmp.retries', 5));
        $maxRepeaters = (int) ($settings['max_repeaters'] ?: LibrenmsConfig::getOsSetting($os, 'snmp.max_repeaters', LibrenmsConfig::get('snmp.max_repeaters', 0)));
        $configuredMaxOid = (int) ($settings['max_oid'] ?: LibrenmsConfig::getOsSetting($os, 'snmp_max_oid', LibrenmsConfig::get('snmp.max_oid', 10)));
        $rawBulk = LibrenmsConfig::getOsSetting($os, 'snmp_bulk', LibrenmsConfig::get('snmp_bulk', true));

        return new static(
            enabled: $method->enabled,
            affectsAvailability: $method->affects_availability,
            version: $secretData['version'],
            community: $secretData['community'] ?? null,
            authname: $secretData['authname'] ?? null,
            authpass: $secretData['authpass'] ?? null,
            authlevel: $secretData['authlevel'],
            authalgo: $secretData['authalgo'],
            cryptopass: $secretData['cryptopass'] ?? null,
            cryptoalgo: $secretData['cryptoalgo'],
            context: $secretData['context'] ?? null,
            transport: $settings['transport'],
            port: (int) ($settings['port'] ?? 161),
            timeout: max(0.1, $timeout),
            retries: max(0, $retries),
            maxRepeaters: max(0, $maxRepeaters),
            maxOid: max(1, $configuredMaxOid),
            bulk: filter_var($rawBulk, FILTER_VALIDATE_BOOLEAN),
        );
    }
}
