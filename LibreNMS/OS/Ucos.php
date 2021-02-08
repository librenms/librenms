<?php
/*
 * Ucos.php
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
use Illuminate\Support\Str;

class Ucos extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $applist = snmp_walk($this->getDeviceArray(), 'SYSAPPL-MIB::sysApplInstallPkgProductName', '-OQv');
        if (Str::contains($applist, 'Cisco Unified CCX Database')) {
            $device->features = 'UCCX';
        } elseif (Str::contains($applist, 'Cisco CallManager')) {
            $device->features = 'CUCM';
        } elseif (Str::contains($applist, 'Cisco Emergency Responder')) {
            $device->features = 'CER';
        } elseif (Str::contains($applist, 'Connection System Agent')) {
            $device->features = 'CUC';
        }
    }
}
