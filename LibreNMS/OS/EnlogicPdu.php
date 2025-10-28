<?php
/**
 * EnlogicPdu.php
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
 * @link       https://www.librenms.org
 *
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\OS;
use SnmpQuery;

class EnlogicPdu extends OS
{

    public function discoverOS(Device $device): void
    {
        // try new mib first, they moved oids in this MIB, so we have to check the contents of the version to make sure
        $pdu2 = SnmpQuery::get([
            'ENLOGIC-PDU2-MIB::pduNamePlatePartNumber.1',
            'ENLOGIC-PDU2-MIB::pduNamePlateSerialNumber.1',
            'ENLOGIC-PDU2-MIB::pduNamePlateFirmwareVersion.1',
        ]);

        if (preg_match('/\d\.\d\.\d/', $pdu2->value('ENLOGIC-PDU2-MIB::pduNamePlateFirmwareVersion.1'))) {
            $device->version = $pdu2->value('ENLOGIC-PDU2-MIB::pduNamePlateFirmwareVersion.1');
            $device->serial = $pdu2->value('ENLOGIC-PDU2-MIB::pduNamePlateSerialNumber.1');
            $device->hardware = $pdu2->value('ENLOGIC-PDU2-MIB::pduNamePlatePartNumber.1');

            return;
        }

        $pdu1 = SnmpQuery::get([
            'ENLOGIC-PDU-MIB::pduNamePlateModelNumber.1',
            'ENLOGIC-PDU-MIB::pduNamePlateSerialNumber.1',
            'ENLOGIC-PDU-MIB::pduNamePlateFirmwareVersion.1',
        ]);

        $device->version = $pdu1->value('ENLOGIC-PDU-MIB::pduNamePlateFirmwareVersion.1');
        $device->serial = $pdu1->value('ENLOGIC-PDU-MIB::pduNamePlateSerialNumber.1');
        $device->hardware = $pdu1->value('ENLOGIC-PDU-MIB::pduNamePlateModelNumber.1');
    }
}
