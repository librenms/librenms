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

class NetworkScanner
{
    /**
     * @param  string[]  $ips
     * @param  callable(string, AddFoundDeviceResult): void  $onResult
     */
    public function scan(array $ips, ScanOptions $options, int $concurrency, callable $onResult): void
    {
        if ($ips === []) {
            return;
        }

        $ping = $options->icmpEnabled ? PingSweep::start($ips) : null;
        $probe = new HostProbe($options, $ping);

        try {
            (new FiberPool)->run($ips, $probe->probe(...), $concurrency, $onResult);
        } finally {
            $ping?->stop();
        }
    }
}
