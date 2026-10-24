<?php

/*
 * Cumulus.php
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

use App\Models\Device;
use App\Models\Vrf;
use Illuminate\Support\Collection;
use SnmpQuery;

class Cumulus extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        $response = \SnmpQuery::next([
            'ENTITY-MIB::entPhysicalDescr',
            'ENTITY-MIB::entPhysicalSoftwareRev',
            'ENTITY-MIB::entPhysicalSerialNum',
        ]);

        $device->hardware = $response->value('ENTITY-MIB::entPhysicalDescr') ?: null;
        $device->serial = $response->value('ENTITY-MIB::entPhysicalSerialNum') ?: null;
        $device->version = preg_replace('/^Cumulus Linux /', '', (string) $response->value('ENTITY-MIB::entPhysicalSoftwareRev')) ?: null;
    }

    /**
     * CUMULUS-BGPVRF-MIB lists the VRFs by their id
     */
    public function discoverVrfs(): Collection
    {
        return SnmpQuery::hideMib()->walk('CUMULUS-BGPVRF-MIB::bgpVrfName')->throwIfConnectionError()->mapTable(fn ($data, $vrfId) => new Vrf([
            'vrf_oid' => (string) $vrfId,
            'vrf_name' => $data['bgpVrfName'] ?? '',
            'mplsVpnVrfDescription' => '',
        ]))->filter(fn (Vrf $vrf) => $vrf->vrf_name !== '')->values();
    }

    public function discoverVrfPorts(Collection $vrfs): Collection
    {
        return new Collection;
    }
}
