<?php

/**
 * NeighborPortResolution.php
 *
 * Implement when this OS advertises port ids to its LLDP/CDP neighbors that
 * do not match its own ifName, ifDescr, or ifIndex.
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

namespace LibreNMS\Interfaces\Discovery;

use App\Models\Port;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Discovery\Neighbors\PortFinder;

interface NeighborPortResolution
{
    /**
     * Another device saw this device as a neighbor, find which of this device's ports it is referring to.
     * Return null to fall back to the generic port matching.
     *
     * @param  Neighbor  $neighbor  this device, as seen by the other device
     * @param  PortFinder  $ports  this device's ports
     */
    public function findNeighborPort(Neighbor $neighbor, PortFinder $ports): ?Port;
}
