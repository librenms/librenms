<?php
/*
 * Jetdirect.php
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

class Jetdirect extends \LibreNMS\OS\Shared\Printer
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $device = $this->getDevice();

        $info = $this->parseDeviceId(snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.11.2.3.9.1.1.7.0', '-OQv'));
        $hardware = $info['MDL'] ?? $info['MODEL'] ?? $info['DES'] ?? $info['DESCRIPTION'];
        if (! empty($hardware)) {
            $hardware = str_ireplace([
                'HP ',
                'Hewlett-Packard ',
                ' Series',
            ], '', $hardware);
            $device->hardware = ucfirst($hardware);
        }
    }
}
