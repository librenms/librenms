<?php

/**
 * HostKey.php
 *
 * What identifies a host to look up: its IPs and MACs
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

namespace App\Http\Controllers\Search\Data;

use LibreNMS\Util\IP;

class HostKey
{
    /**
     * @param  list<IP>  $ips
     * @param  list<string>  $macs
     * @param  string|null  $hostname  the name that resolved to the IPs
     */
    public function __construct(
        public readonly array $ips = [],
        public readonly array $macs = [],
        public readonly ?string $hostname = null,
    ) {
    }

    /**
     * @return list<string>
     */
    public function compressedIps(): array
    {
        return array_map(fn (IP $ip): string => $ip->compressed(), $this->ips);
    }
}
