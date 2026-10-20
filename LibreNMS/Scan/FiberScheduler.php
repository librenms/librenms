<?php

/**
 * FiberScheduler.php
 *
 * Concurrency scheduler for executing host probe fibers and reading fping stream.
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
use LibreNMS\Enum\ScanOutcome;
use Symfony\Component\Process\Process;

class FiberScheduler
{
    private string $fpingBuffer = '';

    /**
     * Run the concurrency scheduler.
     *
     * @param  array<string>  $allIps
     * @param  array<string, bool>  $pingAlive
     *
     * @param-out array<string, bool>  $pingAlive
     *
     * @param  callable(string, ScanOutcome): void  $onResult
     */
    public function run(
        array $allIps,
        ?Process $fpingProcess,
        array &$pingAlive,
        ScanOptions $options,
        int $threads,
        callable $onResult
    ): void {
        $probe = new HostProbe();
        /** @var array<string, array{fiber: Fiber<mixed, mixed, ScanOutcome, Process|array<Process>|null>, process: Process|array<Process>|null}> $active */
        $active = [];
        $ipIndex = 0;
        $totalIps = count($allIps);

        while ($ipIndex < $totalIps || ! empty($active)) {
            if ($fpingProcess !== null) {
                $this->readFpingOutput($fpingProcess, $pingAlive);
            }

            while (count($active) < $threads && $ipIndex < $totalIps) {
                $ip = $allIps[$ipIndex++];
                $fiber = new Fiber(fn () => $probe->probe($ip, $options, $pingAlive, $fpingProcess));
                $process = $fiber->start();

                if ($fiber->isTerminated()) {
                    $onResult($ip, $fiber->getReturn());
                } else {
                    $active[$ip] = ['fiber' => $fiber, 'process' => $process];
                }
            }

            foreach ($active as $ip => $entry) {
                $procs = $entry['process'];
                $isDone = false;

                if ($procs === null) {
                    $isDone = true;
                } elseif ($procs instanceof Process) {
                    $isDone = ! $procs->isRunning();
                } elseif (is_array($procs)) {
                    $anySuccess = false;
                    $allFinished = true;
                    foreach ($procs as $p) {
                        if ($p->isRunning()) {
                            $allFinished = false;
                        } elseif ($p->isSuccessful()) {
                            $anySuccess = true;
                        }
                    }
                    $isDone = $anySuccess || $allFinished;
                }

                if ($isDone) {
                    $fiber = $entry['fiber'];
                    $nextProc = $fiber->resume();

                    if ($fiber->isTerminated()) {
                        $onResult($ip, $fiber->getReturn());
                        unset($active[$ip]);
                    } else {
                        $active[$ip]['process'] = $nextProc;
                    }
                }
            }

            if (! empty($active) || $ipIndex < $totalIps) {
                usleep(1000);
            }
        }

        if ($fpingProcess !== null) {
            $this->readFpingOutput($fpingProcess, $pingAlive);
        }
    }

    /**
     * @param  array<string, bool>  $pingAlive
     *
     * @param-out array<string, bool>  $pingAlive
     */
    private function readFpingOutput(Process $fpingProcess, array &$pingAlive): void
    {
        $output = $fpingProcess->getIncrementalOutput();
        if ($output === '' && $this->fpingBuffer === '') {
            return;
        }

        $this->fpingBuffer .= $output;
        $lines = explode("\n", $this->fpingBuffer);
        $this->fpingBuffer = array_pop($lines) ?? '';

        foreach ($lines as $line) {
            $ip = trim($line);
            if ($ip !== '') {
                $pingAlive[$ip] = true;
            }
        }
    }
}
