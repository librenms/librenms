<?php
/**
 * supermicro-bmc.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;

class SupermicroBmc extends \LibreNMS\OS implements OSDiscovery, OSPolling
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $this->customSysName($device);
    }

    public function pollOS(DataStorageInterface $datastore): void
    {
        $this->customSysName($this->getDevice());
    }

    /**
     * @param  Device  $device
     */
    private function customSysName(Device $device): void
    {
        $device->sysName = \SnmpQuery::get('ATEN-IPMI-MIB::hostName.0')->value() ?: $device->sysName;
    }
}
