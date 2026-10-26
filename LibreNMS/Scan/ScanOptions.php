<?php

/**
 * ScanOptions.php
 *
 * How a network scan probes addresses and adds devices.
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

use App\Actions\Device\BuildPollingMethodCandidates;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;

final readonly class ScanOptions
{
    /**
     * @param  int  $concurrency  how many addresses are probed at once
     * @param  string[]  $excludedNetworks  addresses in these networks are not probed
     * @param  int  $pollerGroup  the poller group devices are added to, 0 for the default
     * @param  bool  $dnsOnly  only add addresses with a reverse DNS name
     * @param  bool  $pingFallback  add hosts that only reply to ping as ping only
     * @param  bool  $pingOnly  add every host that replies to ping as ping only, SNMP is not probed
     * @param  bool  $icmpEnabled  ping the addresses
     * @param  list<array{int, SnmpConfig}>  $snmpConfigs  the credentials to probe with as [secret id, config], most preferred first
     */
    public function __construct(
        public int $concurrency = 32,
        public array $excludedNetworks = [],
        public int $pollerGroup = 0,
        public bool $dnsOnly = false,
        public bool $pingFallback = false,
        public bool $pingOnly = false,
        public bool $icmpEnabled = true,
        public array $snmpConfigs = [],
    ) {
    }

    /**
     * The options with the ICMP setting and default SNMP credentials from the config.
     *
     * @param  string[]  $excludedNetworks
     */
    public static function fromConfig(
        int $concurrency = 32,
        array $excludedNetworks = [],
        int $pollerGroup = 0,
        bool $dnsOnly = false,
        bool $pingFallback = false,
        bool $pingOnly = false,
    ): self {
        return new self(
            concurrency: max($concurrency, 1),
            excludedNetworks: $excludedNetworks,
            pollerGroup: $pollerGroup,
            dnsOnly: $dnsOnly,
            pingFallback: $pingFallback,
            pingOnly: $pingOnly,
            icmpEnabled: (bool) LibrenmsConfig::get('icmp_check', true),
            snmpConfigs: $pingOnly ? [] : self::defaultSnmpConfigs(),
        );
    }

    /**
     * The default SNMP credentials, tried like they are for a new device, with a short timeout and no retries.
     *
     * @return list<array{int, SnmpConfig}> [secret id, config], most preferred first
     */
    public static function defaultSnmpConfigs(): array
    {
        $method = new SnmpPollingMethod;
        $device = new Device;
        $deviceMethod = new DevicePollingMethod(['method_type' => PollingMethodType::Snmp, 'settings' => ['timeout' => 1, 'retries' => 0]]);

        return array_map(
            fn (DevicePollingMethod $candidate): array => [(int) $candidate->secret_id, $method->config($device, $candidate)],
            app(BuildPollingMethodCandidates::class)->execute($device, $deviceMethod),
        );
    }
}
