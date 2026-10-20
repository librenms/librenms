<?php

/**
 * PingSweep.php
 *
 * Ping the scanned addresses in batches, ahead of probing them.
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

use Generator;

class PingSweep
{
    private const BATCH_SIZE = 256;

    /** @var array<string, PingBatch> the batch each address is pinged in, until its result is read */
    private array $batchOf = [];
    /** @var PingBatch[] */
    private array $batches = [];

    /**
     * Yield the addresses, a batch is pinged when its first address is yielded.
     *
     * @param  iterable<string>  $ips
     * @return Generator<int, string>
     */
    public function feed(iterable $ips): Generator
    {
        $batch = [];
        foreach ($ips as $ip) {
            $batch[] = $ip;

            if (count($batch) === self::BATCH_SIZE) {
                yield from $this->start($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            yield from $this->start($batch);
        }
    }

    /**
     * The ping result of an address that was fed.
     *
     * @return bool|null null while fping has not answered for the address yet
     */
    public function result(string $ip): ?bool
    {
        $batch = $this->batchOf[$ip] ?? null;
        if ($batch === null) {
            return false; // not fed
        }

        $result = $batch->result($ip);
        if ($result !== null) {
            unset($this->batchOf[$ip]);
        }

        return $result;
    }

    public function stop(): void
    {
        foreach ($this->batches as $batch) {
            $batch->stop();
        }
    }

    /**
     * @param  string[]  $ips
     * @return string[]
     */
    private function start(array $ips): array
    {
        $batch = new PingBatch($ips);

        // forget finished batches, their remaining results are held by $batchOf
        $this->batches = array_filter($this->batches, fn (PingBatch $batch): bool => $batch->isRunning());
        $this->batches[] = $batch;

        foreach ($ips as $ip) {
            $this->batchOf[$ip] = $batch;
        }

        return $ips;
    }
}
