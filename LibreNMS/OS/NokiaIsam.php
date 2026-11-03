<?php

/**
 * NokiaIsam.php
 *
 * Nokia ISAM
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\OS;

use App\Models\Vrf;
use Illuminate\Support\Collection;
use LibreNMS\OS;

class NokiaIsam extends OS
{
    /**
     * ihub is the routing instance of the integrated hub, it is only reachable through its own SNMP context.
     * The vrf module skips the context on NTs that do not answer it.
     */
    public function discoverVrfs(): Collection
    {
        return new Collection([new Vrf(['vrf_oid' => 'ihub', 'vrf_name' => 'ihub', 'context_name' => 'ihub'])]);
    }

    public function discoverVrfPorts(Collection $vrfs): Collection
    {
        return new Collection;
    }
}
