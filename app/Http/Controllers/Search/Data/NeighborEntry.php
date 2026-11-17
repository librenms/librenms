<?php

/**
 * NeighborEntry.php
 *
 * An ARP or ND table entry for a host
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

use App\Models\Port;
use LibreNMS\Util\IP;

class NeighborEntry
{
    /**
     * @param  string  $source  ARP or ND
     */
    public function __construct(
        public readonly string $source,
        public readonly IP $ip,
        public readonly string $mac,
        public readonly ?Port $port,
        public readonly string $context,
    ) {
    }

    /**
     * The same fact can come from several queries or contexts
     */
    public function key(): string
    {
        return implode('|', [$this->source, $this->ip->compressed(), $this->mac, $this->port?->port_id]);
    }
}
