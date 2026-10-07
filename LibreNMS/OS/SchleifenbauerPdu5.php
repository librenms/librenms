<?php

/*
 * SchleifenbauerPdu5.php
 *
 * Schleifenbauer PDU5 / Enertree gateway (SchleifenbauerGenericMib, enterprise 44568).
 * Distinct from the older SPDM/Databus range (enterprise 31034), which is handled
 * by LibreNMS\OS\Schleifenbauer.
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
 */

namespace LibreNMS\OS;

use App\Models\Device;
use SnmpQuery;

class SchleifenbauerPdu5 extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        // The agent fronts one or more PDUs, indexed 1..pduCount. Identify the device
        // by the first entry in the table; per-PDU detail is carried on the sensors.
        $identification = SnmpQuery::walk('SchleifenbauerGenericMib::deviceIdentificationTable')->table(1);

        $unit = reset($identification);
        if (! is_array($unit)) {
            return;
        }

        $device->hardware = $unit['SchleifenbauerGenericMib::idProduct'] ?? null;
        $device->serial = $unit['SchleifenbauerGenericMib::idSerialNumber'] ?? null;
        $device->version = $unit['SchleifenbauerGenericMib::idModuleFirmware'] ?? null;
    }
}
