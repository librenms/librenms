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

use App\Facades\LibrenmsConfig;
use Fiber;
use Illuminate\Support\Arr;
use LibreNMS\Enum\ScanOutcome;
use LibreNMS\Util\Rewrite;
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
        $shouldAdd = match (true) {
            $options->pingOnly => $isPingable || ! $options->icmpEnabled,
            $snmpAvailable => true,
            $options->pingFallback && $isPingable => true,
            default => false,
        };

        if (! $shouldAdd) {
            return $isPingable ? ScanOutcome::Failed : ScanOutcome::Unpingable;
        }

        // 4. Hand off candidate to non-blocking scan:host worker process
        return $this->addDevice($ip, $options);
    }

    private function probeSnmp(string $ip, ScanOptions $options): bool
    {
        $snmpget = (string) LibrenmsConfig::get('snmpget', 'snmpget');
        $targetHost = Rewrite::addIpv6Brackets($ip);
        $target = "{$options->snmpTransport}:{$targetHost}:{$options->snmpPort}";

        // SNMPv3 unauthenticated discovery probe
        $cmdV3 = [
            $snmpget,
            '-v3',
            '-u', '',
            '-l', 'noAuthNoPriv',
            '-t', '1',
            '-r', '0',
            $target,
            'SNMPv2-MIB::sysDescr.0',
        ];

        // SNMPv2c probe with configured community
        $communities = (array) LibrenmsConfig::get('snmp.community', ['public']);
        $community = (string) (Arr::first($communities) ?: 'public');

        $cmdV2 = [
            $snmpget,
            '-v2c',
            '-c', $community,
            '-t', '1',
            '-r', '0',
            $target,
            'SNMPv2-MIB::sysDescr.0',
        ];

        $processV3 = new Process($cmdV3);
        $processV2 = new Process($cmdV2);

        $processV3->start();
        $processV2->start();

        Fiber::suspend([$processV3, $processV2]);

        $v3Success = $processV3->isSuccessful();
        $v2Success = $processV2->isSuccessful();

        if ($processV3->isRunning()) {
            $processV3->stop(0);
        }
        if ($processV2->isRunning()) {
            $processV2->stop(0);
        }

        return $v3Success || $v2Success;
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

        $exitCode = $process->getExitCode();
        $output = trim($process->getOutput());

        if ($exitCode === 2) {
            return str_contains($output, 'Could not ping') ? ScanOutcome::Unpingable : ScanOutcome::Failed;
        }

        return ScanOutcome::tryFrom($exitCode ?? -1) ?? ScanOutcome::Error;
    }
}
