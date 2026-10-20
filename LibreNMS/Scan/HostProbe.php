<?php

/**
 * HostProbe.php
 *
 * Async per-host scanner using PHP Fibers.
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

use Fiber;
use LibreNMS\Data\Source\Snmp\NetSnmpOptions;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Enum\ScanOutcome;
use Symfony\Component\Process\Process;

class HostProbe
{
    /**
     * Probe a single IP address asynchronously using Fibers and non-blocking sub-processes.
     *
     * @param  array<string, bool>  $pingAlive
     */
    public function probe(string $ip, ScanOptions $options, array &$pingAlive, ?Process $fpingProcess = null): ScanOutcome
    {
        // 1. Probe SNMP for every IP in parallel (unless pingOnly mode)
        $snmpAvailable = false;
        if (! $options->pingOnly) {
            $snmpAvailable = $this->probeSnmp($ip, $options);
        }

        // 2. ICMP ping result from background fping process
        $isPingable = $pingAlive[$ip] ?? false;

        // 3. Candidate to add if SNMP responded OR ping responded
        if ($options->pingOnly) {
            $shouldAdd = $isPingable || ! $options->icmpEnabled;
        } else {
            $shouldAdd = $snmpAvailable || ($options->pingFallback && $isPingable);
        }

        if (! $shouldAdd) {
            return $isPingable ? ScanOutcome::Failed : ScanOutcome::Unpingable;
        }

        // 4. Hand off candidate to non-blocking scan:host worker process
        return $this->addDevice($ip, $options);
    }

    private function probeSnmp(string $ip, ScanOptions $options): bool
    {
        if (empty($options->snmpConfigs)) {
            return false;
        }

        $netSnmp = new NetSnmpOptions;
        $processes = [];
        foreach ($options->snmpConfigs as $config) {
            $process = new Process($netSnmp->buildCli('snmpget', $ip, ['SNMPv2-MIB::sysObjectID.0'], $config, SnmpQueryOptions::quickPrint()));
            $process->start();
            $processes[] = $process;
        }

        Fiber::suspend($processes);

        $success = false;
        foreach ($processes as $process) {
            if ($process->isRunning()) {
                $process->stop(0);
            } elseif (in_array($process->getExitCode(), [0, 2], true)) {
                // same as SnmpPollingMethod::probe(), 2 is an error response, so the host replied
                $success = true;
            }
        }

        return $success;
    }

    private function addDevice(string $ip, ScanOptions $options): ScanOutcome
    {
        $cmd = [PHP_BINARY, base_path('artisan'), 'scan:host', $ip];

        if ($options->pollerGroup !== '0' && $options->pollerGroup !== '') {
            $cmd[] = '-g';
            $cmd[] = $options->pollerGroup;
        }

        if ($options->dnsOnly) {
            $cmd[] = '-o';
        }

        if ($options->pingOnly) {
            $cmd[] = '-P';
        } elseif ($options->pingFallback) {
            $cmd[] = '-b';
        }

        $process = new Process($cmd);
        $process->start();

        Fiber::suspend($process);

        // the exit code is the outcome of the AddScannedHost job
        return ScanOutcome::tryFrom($process->getExitCode() ?? -1) ?? ScanOutcome::Error;
    }
}
