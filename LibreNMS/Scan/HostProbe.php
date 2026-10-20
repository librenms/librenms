<?php

/**
 * HostProbe.php
 *
 * Find out how one address replies and whether to add it.
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

use App\Jobs\AddScannedHost;
use LibreNMS\Enum\DeviceAddOutcome;

final readonly class HostProbe
{
    private ?SnmpProbe $snmp;

    /**
     * Start probing, the probe runs in other processes, check on it with result().
     *
     * @param  PingBatch|null  $ping  the batch pinging the address, null when ICMP checks are disabled
     */
    public function __construct(
        public string $ip,
        private ScanOptions $options,
        private ?PingBatch $ping,
    ) {
        $this->snmp = $options->pingOnly || $options->snmpConfigs === [] ? null : new SnmpProbe($ip, $options->snmpConfigs);
    }

    /**
     * Check on the probe without waiting.
     *
     * @return AddScannedHost|DeviceAddOutcome|null the job that adds the host, why it is not added, or null while waiting for replies
     */
    public function result(): AddScannedHost|DeviceAddOutcome|null
    {
        if ($this->snmp?->settled() === false) {
            return null;
        }

        // also wait for ping when SNMP replied, ICMP is only polled for hosts that reply to ping
        $pingable = $this->ping?->result($this->ip);
        if ($this->ping !== null && $pingable === null) {
            return null;
        }

        $snmpSecretId = $this->snmp?->secretId();

        if ($snmpSecretId !== null || $this->addAsPingOnly($pingable)) {
            return new AddScannedHost(
                ip: $this->ip,
                snmpSecretId: $snmpSecretId,
                pingable: $pingable === true,
                pollerGroup: $this->options->pollerGroup,
                dnsOnly: $this->options->dnsOnly,
            );
        }

        return $pingable === false ? DeviceAddOutcome::Unpingable : DeviceAddOutcome::Unreachable;
    }

    /**
     * @param  bool|null  $pingable  null when not pinged
     */
    private function addAsPingOnly(?bool $pingable): bool
    {
        // without ICMP checks, ping only adds every address, it is pinged when added
        return ($this->options->pingOnly && $pingable !== false)
            || ($this->options->pingFallback && $pingable === true);
    }
}
