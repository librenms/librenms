<?php
/**
 * Brother.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Brother extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $data = snmp_get_multi($this->getDeviceArray(), [
            'brmDNSPrinterName.0', // Brother HL-2070N series
            'brInfoSerialNumber.0', // 000A5J431816
            'brpsFirmwareDescription.0', // Firmware Ver.1.33 (06.07.21)
            'brieee1284id.0', // MFG:Brother;CMD:HBP,PJL,PCL,PCLXL,POSTSCRIPT;MDL:MFC-8440;CLS:PRINTER;
        ], '-OQUs', 'BROTHER-MIB');

        $device->serial = $data[0]['brInfoSerialNumber'] ?? null;

        if (isset($data[0]['brmDNSPrinterName'])) {
            $device->hardware = str_replace(['Brother ', ' series'], '', $data[0]['brmDNSPrinterName']);
        } elseif (isset($data[0]['brieee1284id'])) {
            preg_match('/MDL:([^;]+)/', $data[0]['brieee1284id'], $matches);
            $device->hardware = $matches[1] ?? null;
        }

        if (isset($data[0]['brpsFirmwareDescription'])) {
            preg_match('/Ver\.([^ ]+)/', $data[0]['brpsFirmwareDescription'], $matches);
            $device->version = $matches[1] ?? null;
        }
    }
}
