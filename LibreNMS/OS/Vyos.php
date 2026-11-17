<?php

/**
 * Vyos.php
 *
 * VyOS routers
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

namespace LibreNMS\OS;

use Illuminate\Support\Collection;
use LibreNMS\OS\Shared\Unix;
use LibreNMS\OS\Traits\Bgp4V2Mib;

class Vyos extends Unix
{
    use Bgp4V2Mib;

    /**
     * The FRR BGP4V2-MIB has no message counters
     */
    public function discoverBgpPeers(): Collection
    {
        return $this->discoverBgp4V2Peers('BGP4V2-MIB', 'bgp4V2', counters: false);
    }

    public function discoverBgpLocalAs(): ?int
    {
        return $this->bgpLocalAsFallback(parent::discoverBgpLocalAs(), 'BGP4V2-MIB::bgp4V2PeerLocalAs');
    }
}
