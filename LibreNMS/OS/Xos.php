<?php

/*
 * Xos.php
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
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Port;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Discovery\Neighbors\PortFinder;
use LibreNMS\Interfaces\Discovery\NeighborPortResolution;

class Xos extends Shared\Extreme implements NeighborPortResolution
{
    /**
     * XOS advertises ports as slot:port (or just port when standalone), the ifIndex is slot * 1000 + port
     */
    public function findNeighborPort(Neighbor $neighbor, PortFinder $ports): ?Port
    {
        if (! preg_match('/^(?:(\d+):)?(\d+)$/', $neighbor->portId, $matches)) {
            return null;
        }

        $slot = (int) ($matches[1] ?: 1);

        return $ports->byIfIndex($slot * 1000 + (int) $matches[2]);
    }
}
