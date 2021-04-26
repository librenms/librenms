<?php
/**
 * Terra.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Terra extends OS implements ProcessorDiscovery, OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $models = [
            'sda410C' => '5',
            'sta410C' => '6',
            'saa410C' => '7',
            'sdi410C' => '8',
            'sti410C' => '9',
            'sai410C' => '10',
            'ttd440' => '14',
            'ttx410C' => '15',
            'tdx410C' => '16',
            'sdi480' => '17',
            'sti440' => '18',
        ];

        foreach ($models as $model => $index) {
            if (Str::contains($device->sysDescr, $model)) {
                $oid_terra = '.1.3.6.1.4.1.30631.1.';
                $oid = [$oid_terra . $index . '.4.1.0', $oid_terra . $index . '.4.2.0'];

                $data = snmp_get_multi_oid($device, $oid);
                $device->hardware = $model;
                $device->version = $data[$oid[0]] ?? null;
                $device->version = $data[$oid[1]] ?? null;
                break;
            }
        }
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $device = $this->getDeviceArray();

        $query = [
            'sti410C' => '.1.3.6.1.4.1.30631.1.9.1.1.3.0',
            'sti440' => '.1.3.6.1.4.1.30631.1.18.1.326.3.0',
        ];

        foreach ($query as $decr => $oid) {
            if (strpos($device['sysDescr'], $decr) !== false) {
                return [
                    Processor::discover(
                        'cpu',
                        $this->getDeviceId(),
                        $oid,
                        0
                    ),
                ];
            }
        }

        return [];
    }
}
