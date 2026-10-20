<?php

/**
 * HostProbe.php
 *
 * Check if a scanned address replies and add it as a device.
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
use LibreNMS\Data\Source\Snmp\NetSnmpOptions;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Enum\DeviceAddOutcome;
use Symfony\Component\Process\Process;

class HostProbe
{
    /**
     * @param  PingSweep|null  $ping  null when ICMP checks are disabled
     */
    public function __construct(
        private readonly ScanOptions $options,
        private readonly ?PingSweep $ping,
    ) {
    }

    /**
     * Find out how the address replies, run in a FiberPool so many addresses are probed at once.
     *
     * @return AddScannedHost|DeviceAddOutcome the job to add the host, or why it is not added
     */
    public function probe(string $ip): AddScannedHost|DeviceAddOutcome
    {
        $snmpSecretId = $this->options->pingOnly ? null : $this->snmpSecretId($ip);
        $pingable = $this->ping?->isAlive($ip); // null when not pinged

        if ($snmpSecretId !== null) {
            return $this->add($ip, $snmpSecretId, $pingable);
        }

        if ($this->options->pingOnly && $pingable !== false) {
            return $this->add($ip, null, $pingable);
        }

        if ($this->options->pingFallback && $pingable) {
            return $this->add($ip, null, $pingable);
        }

        return $pingable === false ? DeviceAddOutcome::Unpingable : DeviceAddOutcome::Unreachable;
    }

    /**
     * Try all credentials at once, the same one is picked as when trying them in order.
     *
     * @return int|null the id of the first secret in order that got a reply
     */
    private function snmpSecretId(string $ip): ?int
    {
        $netSnmp = new NetSnmpOptions;
        $processes = [];
        foreach ($this->options->snmpConfigs as $secretId => $config) {
            $process = new Process($netSnmp->buildCli('snmpget', $ip, ['SNMPv2-MIB::sysObjectID.0'], $config, SnmpQueryOptions::quickPrint()));
            $process->start();
            $processes[$secretId] = $process;
        }

        $secretId = null;
        FiberPool::waitUntil(function () use ($processes, &$secretId): bool {
            foreach ($processes as $id => $process) {
                if ($process->isRunning()) {
                    return false; // wait, a preferred credential may still reply
                }

                // same as SnmpPollingMethod::probe(), exit code 2 is an error response
                if (in_array($process->getExitCode(), [0, 2], true)) {
                    $secretId = $id;

                    return true;
                }
            }

            return true; // no reply
        });

        foreach ($processes as $process) {
            $process->stop(0);
        }

        return $secretId;
    }

    private function add(string $ip, ?int $snmpSecretId, ?bool $pingable): AddScannedHost
    {
        return new AddScannedHost(
            ip: $ip,
            snmpSecretId: $snmpSecretId,
            pingable: $pingable === true,
            pollerGroup: $this->options->pollerGroup,
            dnsOnly: $this->options->dnsOnly,
        );
    }
}
