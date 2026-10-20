<?php

/**
 * ScanOptions.php
 *
 * Options object for network scanning.
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

namespace LibreNMS\Scan;

use App\Facades\LibrenmsConfig;
use App\Models\Secret;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;

readonly class ScanOptions
{
    /**
     * @param  SnmpConfig[]  $snmpConfigs  credentials to probe each host with, tried in parallel
     */
    public function __construct(
        public string $pollerGroup = '0',
        public bool $dnsOnly = false,
        public bool $pingFallback = false,
        public bool $pingOnly = false,
        public bool $icmpEnabled = true,
        public string $fpingBin = 'fping',
        public int $fpingTimeout = 500,
        public array $snmpConfigs = [],
    ) {
    }

    public static function fromConfig(
        string $pollerGroup = '0',
        bool $dnsOnly = false,
        bool $pingFallback = false,
        bool $pingOnly = false,
    ): self {
        return new self(
            pollerGroup: $pollerGroup,
            dnsOnly: $dnsOnly,
            pingFallback: $pingFallback,
            pingOnly: $pingOnly,
            icmpEnabled: (bool) LibrenmsConfig::get('icmp_check', true),
            fpingBin: (string) LibrenmsConfig::get('fping', 'fping'),
            fpingTimeout: (int) LibrenmsConfig::get('fping_options.timeout', 500),
            snmpConfigs: $pingOnly ? [] : self::defaultSnmpConfigs(),
        );
    }

    /**
     * The default SNMP credentials, the same ones device:add tries, with a short timeout and no retries.
     *
     * @return SnmpConfig[]
     */
    public static function defaultSnmpConfigs(): array
    {
        $method = new SnmpPollingMethod;
        $settings = ['timeout' => 1, 'retries' => 0] + $method->defaults();

        return $method->defaultSecrets()
            ->map(fn (Secret $secret) => SnmpConfig::make($settings, SnmpSecretData::fromArray($secret->data ?? [])))
            ->all();
    }
}
