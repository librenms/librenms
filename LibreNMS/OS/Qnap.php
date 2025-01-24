<?php
/**
 * Qnap.php
 *
 * QNAP Turbo NAS OS
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
 * @copyright  2020 Daniel Baeza
 * @author     Daniel Baeza <doctoruve@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Qnap extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $response = SnmpQuery::next([
            'NAS-MIB::enclosureModel',
            'NAS-MIB::enclosureSerialNum',
            'ENTITY-MIB::entPhysicalFirmwareRev',
        ]);

        $device->version = trim($response->value('ENTITY-MIB::entPhysicalFirmwareRev'), '\"') ?: null;
        $device->hardware = $response->value('NAS-MIB::enclosureModel') ?: null;
        $device->serial = $response->value('NAS-MIB::enclosureSerialNum') ?: null;
    }
}
