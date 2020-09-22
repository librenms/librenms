<?php
/**
 * Edgecos.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Edgecos extends OS implements ProcessorDiscovery
{
    public function discoverOS(Device $device): void
    {
        if (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.6.')) {              //ES3528M0
            $tmp_mib = 'ES3528MO-MIB';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.22.')) {  //ES3528MV2
            $tmp_mib = 'ES3528MV2-MIB';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.24.')) {  //ECS4510
            $tmp_mib = 'ECS4510-MIB';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.39.')) {  //ECS4110
            $tmp_mib = 'ECS4110-MIB';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.42.')) {  //ECS4210
            $tmp_mib = 'ECS4210-MIB';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.27.')) {  //ECS3510
            $tmp_mib = 'ECS3510-MIB';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.45.')) {  //ECS4120
            $tmp_mib = 'ECS4120-MIB';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.8.1.11')) {    //ES3510MA
            $tmp_mib = 'ES3510MA-MIB';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.43.')) {  //ECS2100
            $tmp_mib = 'ECS2100-MIB';
        } else {
            return;
        }

        $data = snmp_get_multi($this->getDeviceArray(), ['swOpCodeVer.1', 'swProdName.0', 'swSerialNumber.1', 'swHardwareVer.1'], '-OQUs', $tmp_mib);

        $device->version = trim($data[1]['swHardwareVer'] . ' ' . $data[1]['swOpCodeVer']) ?: null;
        $device->hardware = $data[0]['swProdName'] ?? null;
        $device->serial = $data[1]['swSerialNumber'] ?? null;
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $device = $this->getDevice();

        if (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.24.')) { //ECS4510
            $oid = '.1.3.6.1.4.1.259.10.1.24.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.22.')) { //ECS3528
            $oid = '.1.3.6.1.4.1.259.10.1.22.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.39.')) { //ECS4110
            $oid = '.1.3.6.1.4.1.259.10.1.39.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.45.')) { //ECS4120
            $oid = '.1.3.6.1.4.1.259.10.1.45.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.42.')) { //ECS4210
            $oid = '.1.3.6.1.4.1.259.10.1.42.101.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.27.')) { //ECS3510
            $oid = '.1.3.6.1.4.1.259.10.1.27.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.8.1.11.')) { //ES3510MA
            $oid = '.1.3.6.1.4.1.259.8.1.11.1.39.2.1.0';
        }

        if (isset($oid)) {
            return [
                Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    $oid,
                    0
                ),
            ];
        }

        return [];
    }
}
