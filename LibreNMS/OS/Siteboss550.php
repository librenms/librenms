<?php
/**
 * Siteboss550.php
 *
 * Asentria Siteboss 550
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 */

namespace LibreNMS\OS;

use LibreNMS\OS;
use LibreNMS\Interfaces\Discovery\OSDiscovery;

class Siteboss550 extends OS implements
    OSDiscovery
{
    public function discoverOS(): void
    {
        $deviceModel = $this->getDeviceModel();
        $deviceModel->version = preg_replace('/^\s*(\S+\s+\S+\s+)/', '', $deviceModel['sysDescr']);
        preg_match('/^\S+\s+\d+\s+/', $deviceModel['sysDescr'], $matches);
        $deviceModel->hardware = trim($matches[0]);
        $deviceModel->sysName = snmp_get($this->getDevice(), 'siteName.0', '-Osqnv', 'SITEBOSS-550-STD-MIB');
    }
}
