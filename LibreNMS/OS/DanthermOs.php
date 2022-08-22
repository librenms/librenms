<?php
/**
 * DanthermOs.php
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
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Polling\OSPolling;

class DanthermOs extends \LibreNMS\OS implements OSPolling
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $this->customSysName($device);
    }

    public function pollOs(): void
    {
        $this->customSysName($this->getDevice());
    }

    /**
     * @param  \App\Models\Device  $device
     */
    private function customSysName(Device $device): void
    {
        $device->sysName = snmp_get($this->getDeviceArray(), 'hostName.0', '-Osqnv', 'DANTHERM-COOLING-MIB') ?: $device->sysName;
    }
}
