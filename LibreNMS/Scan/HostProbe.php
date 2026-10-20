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

use App\Actions\Device\AddFoundDeviceResult;
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
     * Run in a FiberPool, waiting for replies lets the other addresses be probed.
     */
    public function probe(string $ip): AddFoundDeviceResult
    {
        if (! $this->options->pingOnly && $this->repliesToSnmp($ip)) {
            return $this->add($ip);
        }

        $pingable = $this->ping?->isAlive($ip);

        if ($this->options->pingOnly && $pingable !== false) {
            return $this->add($ip);
        }

        if ($this->options->pingFallback && $pingable) {
            return $this->add($ip);
        }

        return new AddFoundDeviceResult($pingable === false ? DeviceAddOutcome::Unpingable : DeviceAddOutcome::Unreachable);
    }

    /**
     * Try all credentials at once, stop at the first reply.
     */
    private function repliesToSnmp(string $ip): bool
    {
        $netSnmp = new NetSnmpOptions;
        $processes = [];
        foreach ($this->options->snmpConfigs as $config) {
            $process = new Process($netSnmp->buildCli('snmpget', $ip, ['SNMPv2-MIB::sysObjectID.0'], $config, SnmpQueryOptions::quickPrint()));
            $process->start();
            $processes[] = $process;
        }

        // same as SnmpPollingMethod::probe(), exit code 2 is an error response
        $replied = fn (Process $process): bool => ! $process->isRunning() && in_array($process->getExitCode(), [0, 2], true);
        $running = fn (Process $process): bool => $process->isRunning();

        FiberPool::waitUntil(fn (): bool => array_any($processes, $replied) || ! array_any($processes, $running));

        foreach ($processes as $process) {
            $process->stop(0);
        }

        return array_any($processes, $replied);
    }

    /**
     * Add the device in a scan:host process, so many can be added at once.
     */
    private function add(string $ip): AddFoundDeviceResult
    {
        $cmd = [PHP_BINARY, base_path('artisan'), 'scan:host', $ip];

        if ($this->options->pollerGroup > 0) {
            array_push($cmd, '--poller-group', (string) $this->options->pollerGroup);
        }

        if ($this->options->dnsOnly) {
            $cmd[] = '--dns-only';
        }

        if ($this->options->pingOnly) {
            $cmd[] = '--ping-only';
        } elseif ($this->options->pingFallback) {
            $cmd[] = '--ping-fallback';
        }

        $process = new Process($cmd);
        $process->start();

        FiberPool::waitUntil(fn (): bool => ! $process->isRunning());

        // scan:host exits with the outcome and prints the message
        return new AddFoundDeviceResult(
            DeviceAddOutcome::tryFrom($process->getExitCode() ?? -1) ?? DeviceAddOutcome::Error,
            trim($process->getOutput() . $process->getErrorOutput()),
        );
    }
}
