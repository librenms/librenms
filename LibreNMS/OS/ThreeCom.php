<?php
/**
 * ThreeCom.php
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
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class ThreeCom extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        if (Str::contains($device->sysDescr, 'Software')) {
            $device->hardware = str_replace('3Com ', '', substr($device->sysDescr, 0, strpos($device->sysDescr, 'Software')));
            // Version is the last word in the sysDescr's first line
            [$device->version] = explode("\n", substr($device->sysDescr, (strpos($device->sysDescr, 'Version') + 8)));

            return;
        }

        $device->hardware = str_replace('3Com ', '', $device->sysDescr);
        // Old Stack Units
        if (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.43.10.27.4.1.')) {
            $oids = ['stackUnitDesc.1', 'stackUnitPromVersion.1', 'stackUnitSWVersion.1', 'stackUnitSerialNumber.1', 'stackUnitCapabilities.1'];
            $data = snmp_get_multi($this->getDeviceArray(), $oids, ['-OQUs', '--hexOutputLength=0'], 'A3COM0352-STACK-CONFIG');
            $device->hardware = trim($device->hardware . ' ' . $data[1]['stackUnitDesc']);
            $device->version = $data[1]['stackUnitSWVersion'];
            $device->serial = $data[1]['stackUnitSerialNumber'];
            $device->features = $data[1]['stackUnitCapabilities'];
        }
    }
}
