<?php

/**
 * SnmpProbe.php
 *
 * Probe an address with several SNMP credentials at once.
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

use LibreNMS\Data\Source\Snmp\NetSnmpOptions;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use Symfony\Component\Process\Process;

final class SnmpProbe
{
    /** @var array<int, Process> by secret id, most preferred first */
    private array $processes = [];
    private ?int $secretId = null;
    private bool $settled = false;

    /**
     * @param  array<int, SnmpConfig>  $configs  by secret id, most preferred first
     */
    public function __construct(string $ip, array $configs)
    {
        $netSnmp = new NetSnmpOptions;

        foreach ($configs as $secretId => $config) {
            $process = new Process($netSnmp->buildCli('snmpget', $ip, ['SNMPv2-MIB::sysObjectID.0'], $config, SnmpQueryOptions::quickPrint()));
            $process->start();
            $this->processes[$secretId] = $process;
        }
    }

    /**
     * Settled once the most preferred credential that replies is known, or none replied.
     * The same credential is picked as trying them one at a time, without waiting for the less preferred ones.
     */
    public function settled(): bool
    {
        if ($this->settled) {
            return true;
        }

        foreach ($this->processes as $secretId => $process) {
            if ($process->isRunning()) {
                return false; // a more preferred credential may still reply
            }

            // same as SnmpPollingMethod::probe(), exit code 2 is an error response, the host replied
            if (in_array($process->getExitCode(), [0, 2], true)) {
                $this->secretId = $secretId;
                break;
            }
        }

        $this->processes = []; // stops the processes that are still running
        $this->settled = true;

        return true;
    }

    /**
     * The secret the host replied to, null if it did not reply or the probe has not settled.
     */
    public function secretId(): ?int
    {
        return $this->secretId;
    }
}
