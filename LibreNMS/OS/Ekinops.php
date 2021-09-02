<?php
/**
 * Ekinops.php
 *
 * Ekinops Optical Network
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
 * @copyright  KanREN, Inc 2020
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Ekinops extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $sysDescr = $device->sysDescr;
        $info = explode(',', $sysDescr);

        $device->hardware = trim($info[1]);
        $device->version = trim($info[2]);

        $mgmtCard = snmp_get($this->getDeviceArray(), 'mgnt2RinvHwPlatform.0', '-OQv', 'EKINOPS-MGNT2-MIB');
        $mgmtInfo = self::ekinopsInfo($mgmtCard);
        $device->serial = $mgmtInfo['Serial Number'];
    }

    /**
     * Parses Ekinops inventory returned in a tabular format within a single OID
     * @param string $ekiInfo
     * @return array $inv
     */
    public static function ekinopsInfo($ekiInfo)
    {
        $info = explode("\n", $ekiInfo);
        unset($info[0]);
        $inv = [];
        foreach ($info as $line) {
            [$attr, $value] = explode(':', $line);
            $attr = trim($attr);
            $value = trim($value);
            $inv[$attr] = $value;
        }

        return $inv;
    }
}
