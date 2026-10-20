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
    /** @var array<int, Process> by secret id, in the order the credentials are preferred */
    private array $snmp = [];
    private ?int $snmpSecretId = null;
    private bool $snmpSettled;

    /**
     * Start probing the address, check on it with result().
     *
     * @param  PingSweep|null  $ping  null when ICMP checks are disabled
     */
    public function __construct(
        public readonly string $ip,
        private readonly ScanOptions $options,
        private readonly ?PingSweep $ping,
    ) {
        if (! $options->pingOnly) {
            $netSnmp = new NetSnmpOptions;
            foreach ($options->snmpConfigs as $secretId => $config) {
                $process = new Process($netSnmp->buildCli('snmpget', $ip, ['SNMPv2-MIB::sysObjectID.0'], $config, SnmpQueryOptions::quickPrint()));
                $process->start();
                $this->snmp[$secretId] = $process;
            }
        }

        $this->snmpSettled = $this->snmp === [];
    }

    /**
     * Check on the probe without waiting.
     *
     * @return AddScannedHost|DeviceAddOutcome|null the job to add the host, why it is not added, or null while waiting for replies
     */
    public function result(): AddScannedHost|DeviceAddOutcome|null
    {
        if (! $this->snmpSettled && ! $this->settleSnmp()) {
            return null;
        }

        $pingable = $this->ping?->result($this->ip);
        if ($this->ping !== null && $pingable === null) {
            return null;
        }

        if ($this->snmpSecretId !== null) {
            return $this->add($this->snmpSecretId, $pingable);
        }

        if ($this->options->pingOnly && $pingable !== false) {
            return $this->add(null, $pingable);
        }

        if ($this->options->pingFallback && $pingable) {
            return $this->add(null, $pingable);
        }

        return $pingable === false ? DeviceAddOutcome::Unpingable : DeviceAddOutcome::Unreachable;
    }

    /**
     * Pick the same credential as trying them in order would, without waiting for the less preferred ones.
     *
     * @return bool false while a preferred credential may still reply
     */
    private function settleSnmp(): bool
    {
        foreach ($this->snmp as $secretId => $process) {
            if ($process->isRunning()) {
                return false;
            }

            // same as SnmpPollingMethod::probe(), exit code 2 is an error response
            if (in_array($process->getExitCode(), [0, 2], true)) {
                $this->snmpSecretId = $secretId;
                break;
            }
        }

        foreach ($this->snmp as $process) {
            $process->stop(0);
        }
        $this->snmpSettled = true;

        return true;
    }

    private function add(?int $snmpSecretId, ?bool $pingable): AddScannedHost
    {
        return new AddScannedHost(
            ip: $this->ip,
            snmpSecretId: $snmpSecretId,
            pingable: $pingable === true,
            pollerGroup: $this->options->pollerGroup,
            dnsOnly: $this->options->dnsOnly,
        );
    }
}
