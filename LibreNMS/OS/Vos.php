<?php

/**
 * Vos.php
 *
 * Versa Operating System
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

class Vos extends OS
{
    /**
     * Routing instances are only listed by the interfaces in them, so they are identified by name
     */
    public function discoverVrfs(): Collection
    {
        return collect($this->vrfNamesByIfIndex())->unique()->map(fn (string $name) => new Vrf([
            'vrf_oid' => $name,
            'vrf_name' => $name,
            'mplsVpnVrfDescription' => '',
        ]))->values();
    }

    public function discoverVrfPorts(Collection $vrfs): Collection
    {
        return $this->vrfMemberPorts($this->vrfNamesByIfIndex(), $vrfs);
    }

    /**
     * @return array<int, string>
     */
    private function vrfNamesByIfIndex(): array
    {
        return array_filter(SnmpQuery::cache()->hideMib()->walk('VERSA-IF-MIB::versaIfVIfVrfName')->throwIfConnectionError()->pluck('versaIfVIfVrfName'), fn ($name) => $name !== '');
    }
}
