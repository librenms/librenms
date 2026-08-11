<?php

/**
 * NetworkScanner.php
 *
 * Parallel network scanner using fping streaming and Fiber concurrency.
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

use LibreNMS\Enum\ScanOutcome;
use Symfony\Component\Process\Process;

class NetworkScanner
{
    /**
     * Perform parallel network scan.
     *
     * @param  array<string>  $allIps
     * @param  callable(string, ScanOutcome): void  $onResult
     */
    public function scan(array $allIps, ScanOptions $options, int $threads, callable $onResult): void
    {
        $pingAlive = [];
        $fpingProcess = null;

        if ($options->icmpEnabled && ! empty($allIps)) {
            $fpingCmd = [
                $options->fpingBin,
                '-a',
                '-q',
                '-t', (string) $options->fpingTimeout,
                '-r', '0',
                '-f', '-',
            ];

            $fpingProcess = new Process($fpingCmd);
            $fpingProcess->setInput(implode("\n", $allIps) . "\n");
            $fpingProcess->start();
        }

        $scheduler = new FiberScheduler();
        $scheduler->run($allIps, $fpingProcess, $pingAlive, $options, $threads, $onResult);

        if ($fpingProcess !== null && $fpingProcess->isRunning()) {
            $fpingProcess->stop(0);
        }
    }
}
