<?php

/**
 * DellOs10.php
 *
 * Dell EMC Networking OS10
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
use LibreNMS\Interfaces\Discovery\BgpAddressFamilyDiscovery;
use LibreNMS\OS;
use LibreNMS\OS\Traits\Bgp4V2Mib;

class DellOs10 extends OS implements BgpAddressFamilyDiscovery
{
    use Bgp4V2Mib;

    /**
     * The instance of the sessions is their VRF
     */
    public function discoverBgpPeers(): Collection
    {
        return $this->discoverBgp4V2Peers('DELLEMC-OS10-BGP4V2-MIB', 'os10bgp4V2', instanceIsVrf: true);
    }

    public function discoverBgpAddressFamilies(Collection $peers): Collection
    {
        return $this->discoverBgp4V2AddressFamilies($peers, 'DELLEMC-OS10-BGP4V2-MIB', 'os10bgp4V2');
    }

    public function discoverBgpLocalAs(): ?int
    {
        return $this->bgpLocalAsFallback(parent::discoverBgpLocalAs(), 'DELLEMC-OS10-BGP4V2-MIB::os10bgp4V2PeerLocalAs');
    }
}
