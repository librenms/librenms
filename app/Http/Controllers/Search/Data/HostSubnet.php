<?php

/**
 * HostSubnet.php
 *
 * What surrounds a host: its gateways and the other interfaces on its subnet
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

use Illuminate\Support\Collection;

class HostSubnet
{
    /**
     * @param  Collection<int, Gateway>  $gateways
     * @param  Collection<int, SubnetAddress>  $peers  interfaces on the subnet, without the gateways
     * @param  string|null  $network  the host's network in cidr format
     */
    public function __construct(
        public readonly Collection $gateways,
        public readonly Collection $peers,
        public readonly ?string $network,
    ) {
    }

    public function count(): int
    {
        return $this->gateways->count() + $this->peers->count();
    }

    /**
     * How many of those past the first $limit are interfaces on the network, the rest can't be listed by searching it
     */
    public function hiddenOnNetwork(int $limit): int
    {
        if ($this->network === null) {
            return 0;
        }

        return $this->gateways->concat($this->peers)->skip($limit)
            ->filter(fn (Gateway|SubnetAddress $item): bool => $item->port !== null && $item->ip->inNetwork($this->network))
            ->count();
    }
}
