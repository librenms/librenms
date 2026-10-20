<?php

/**
 * NetworkScanner.php
 *
 * Probe many addresses at once and add the devices that reply.
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
use App\Jobs\AddScannedHost;
use Illuminate\Support\Facades\Bus;

class NetworkScanner
{
    /**
     * Probe the addresses concurrently and add the hosts that reply, one at a time.
     *
     * @param  iterable<string>  $ips  read as the scan goes, it can be a generator
     * @param  callable(string, AddFoundDeviceResult): void  $onResult
     */
    public function scan(iterable $ips, ScanOptions $options, int $concurrency, callable $onResult): void
    {
        $ping = $options->icmpEnabled ? new PingSweep : null;
        $pending = (fn () => yield from $ping?->feed($ips) ?? $ips)();
        /** @var HostProbe[] $active */
        $active = [];

        try {
            while ($pending->valid() || $active !== []) {
                while (count($active) < $concurrency && $pending->valid()) {
                    $active[] = new HostProbe($pending->current(), $options, $ping);
                    $pending->next();
                }

                foreach ($active as $key => $probe) {
                    $result = $probe->result();
                    if ($result === null) {
                        continue;
                    }

                    unset($active[$key]);
                    // the probes keep running in their own processes while a host is added
                    $onResult($probe->ip, $result instanceof AddScannedHost ? Bus::dispatchNow($result) : new AddFoundDeviceResult($result));
                }

                if ($active !== []) {
                    usleep(1000);
                }
            }
        } finally {
            $ping?->stop();
        }
    }
}
