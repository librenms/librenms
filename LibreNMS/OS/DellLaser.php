<?php
/*
 * DellLaser.php
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

class DellLaser extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        // SNMPv2-SMI::enterprises.253.8.51.10.2.1.7.2.28110202 = STRING: "MFG:Dell;CMD:PJL,RASTER,DOWNLOAD,PCLXL,PCL,POSTSCRIPT;MDL:Laser Printer
        // 3100cn;DES:Dell Laser Printer 3100cn;CLS:PRINTER;STS:AAAMAwAAAAAAAgJ/HgMKBigDCgY8AwAzcJqwggAAwAAACAAAAAAA/w==;"
        $modelinfo = explode(';', snmp_get($this->getDeviceArray(), '1.3.6.1.4.1.253.8.51.10.2.1.7.2.28110202', '-OQv'));

        // SNMPv2-SMI::enterprises.674.10898.100.2.1.2.1.3.1 = STRING: "COMMAND SET:;MODEL:Dell Laser Printer 5310n"
        $modelinfo = array_merge($modelinfo, explode(';', snmp_get($this->getDeviceArray(), '1.3.6.1.4.1.674.10898.100.2.1.2.1.3.1', '-OQv', '', '')));

        // SNMPv2-SMI::enterprises.641.2.1.2.1.3.1 = STRING: "COMMAND SET:;MODEL:Dell Laser Printer 1700n"
        $modelinfo = array_merge($modelinfo, explode(';', snmp_get($this->getDeviceArray(), '1.3.6.1.4.1.641.2.1.2.1.3.1', '-OQv', '', '')));

        $dell_laser = [];
        foreach ($modelinfo as $line) {
            [$key, $value] = explode(':', $line);
            $dell_laser[$key] = $value;
        }

        $device->hardware = $dell_laser['MDL'] ?: $dell_laser['MODEL'];
    }
}
