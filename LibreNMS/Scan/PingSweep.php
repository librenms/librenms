<?php

/**
 * PingSweep.php
 *
 * Ping all scanned addresses with one fping process.
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

use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingOutputBuffer;
use Symfony\Component\Process\Process;

class PingSweep
{
    /** @var array<string, bool> */
    private array $alive = [];
    private readonly FpingOutputBuffer $buffer;

    private function __construct(
        private readonly Process $process,
    ) {
        $this->buffer = new FpingOutputBuffer;
    }

    /**
     * @param  string[]  $ips
     */
    public static function start(array $ips): self
    {
        $process = app(Fping::class)->bulkPingProcess($ips);
        $process->setTimeout(null); // pinging a large network takes longer than the polling timeout
        $process->start();

        return new self($process);
    }

    /**
     * Wait for the ping result of the address.
     */
    public function isAlive(string $ip): bool
    {
        FiberPool::waitUntil(fn (): bool => $this->read() || isset($this->alive[$ip]));

        return $this->alive[$ip] ?? false;
    }

    public function stop(): void
    {
        if ($this->process->isRunning()) {
            $this->process->stop(0);
        }
    }

    /**
     * Read the results so far.
     *
     * @return bool true once fping has finished
     */
    private function read(): bool
    {
        $finished = ! $this->process->isRunning();

        $responses = [
            ...$this->buffer->push(Process::OUT, $this->process->getIncrementalOutput()),
            ...$this->buffer->push(Process::ERR, $this->process->getIncrementalErrorOutput()),
            ...($finished ? $this->buffer->flush() : []),
        ];

        foreach ($responses as $response) {
            if ($response->host !== null) {
                $this->alive[$response->host] = $response->isAlive();
            }
        }

        return $finished;
    }
}
