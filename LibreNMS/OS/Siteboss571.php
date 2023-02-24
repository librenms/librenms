<?php
/**
 * Siteboss.php
 *
 * Asentria Siteboss
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
 * @link       http://librenms.org
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\OS;

class Siteboss571 extends OS
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $device->sysName = snmp_get($this->getDeviceArray(), 'siteName.0', '-Osqnv', 'SITEBOSS-571-STD-MIB');
    }
}
