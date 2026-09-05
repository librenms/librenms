<?php

/*
 * Ping.php
 *
 * -Description-
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
 * @package    LibreNMS
 * @link       https://librenms.org
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\Data\Source\Icmp;

use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\AddressFamily;
use Log;
use Symfony\Component\Process\Process;

class Ping
{
    private readonly string $ping_bin;

    public function __construct()
    {
        $this->ping_bin = LibrenmsConfig::get('ping', 'ping');
    }

    /**
     * Test MTU by sending a ping with a specific payload size.
     *
     * @param  string  $host  hostname or ip
     * @param  int  $size  packet size in bytes (headers included)
     */
    public function testMtu(string $host, int $size, AddressFamily $address_family = AddressFamily::IPv4): bool
    {
        $bytes = $size > 28 ? $size - 28 : $size;

        $args = [
            '-c', '1',
            '-M', 'dont',
            '-s', (string) $bytes,
            '-w', '4',
            $host,
        ];
        $cmd = array_merge($this->pingCommand($address_family), $args);

        Log::debug('[MTU] ' . implode(' ', $cmd) . PHP_EOL);

        $process = app()->make(Process::class, ['command' => $cmd]);
        $process->disableOutput();
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Get the fping command for a given address family
     *
     * @return string[]
     */
    private function pingCommand(?AddressFamily $af = null): array
    {
        return match ($af) {
            AddressFamily::IPv4 => [$this->ping_bin, '-4'],
            AddressFamily::IPv6 => [$this->ping_bin, '-6'],
            default => [$this->ping_bin],
        };
    }
}
