<?php

/**
 * PingBatch.php
 *
 * Ping a batch of addresses with one fping process.
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

final class PingBatch
{
    /** @var array<string, bool> results that were not read yet */
    private array $alive = [];
    private bool $finished = false;
    private readonly FpingOutputBuffer $buffer;
    private readonly Process $process;

    /**
     * Start pinging, fping reads all addresses before it sends any pings, so many small batches beat one large one.
     *
     * @param  string[]  $ips
     */
    public function __construct(array $ips)
    {
        $this->buffer = new FpingOutputBuffer;
        $this->process = app(Fping::class)->bulkPingProcess($ips);
        $this->process->start();
    }

    /**
     * The ping result of an address in the batch as of the last poll(), each result can be read once.
     *
     * @return bool|null null while fping has not answered for the address yet
     */
    public function result(string $ip): ?bool
    {
        if (isset($this->alive[$ip])) {
            $alive = $this->alive[$ip];
            unset($this->alive[$ip]);

            return $alive;
        }

        return $this->finished ? false : null;
    }

    /**
     * Read the output fping has written so far.
     *
     * @return bool true once fping has finished and all of its output was read
     */
    public function poll(): bool
    {
        if ($this->finished) {
            return true;
        }

        $finished = ! $this->process->isRunning(); // check before reading, so no output comes after the last read

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

        return $this->finished = $finished;
    }
}
