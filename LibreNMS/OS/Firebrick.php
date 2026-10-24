<?php

/**
 * Firebrick.php
 *
 * Firebrick routers
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

use App\Models\Vrf;
use Illuminate\Support\Collection;
use LibreNMS\OS;
use SnmpQuery;

class Firebrick extends OS
{
    /**
     * Routing tables are only listed by the BGP sessions in them, table 0 is the default
     */
    public function discoverVrfs(): Collection
    {
        return collect(SnmpQuery::hideMib()->walk('FIREBRICK-BGP-MIB::fbBgpPeerTableId')->throwIfConnectionError()->values())
            ->unique()
            ->filter(fn ($tableId) => is_numeric($tableId) && $tableId > 0)
            ->map(fn ($tableId) => new Vrf(['vrf_oid' => (string) $tableId, 'vrf_name' => (string) $tableId, 'mplsVpnVrfDescription' => '']))
            ->values();
    }

    public function discoverVrfPorts(Collection $vrfs): Collection
    {
        return new Collection;
    }
}
