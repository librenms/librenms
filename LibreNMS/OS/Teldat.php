<?php
/**
 * Teldat.php
 *
 * Teldat Devices
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
 * @copyright  2021 Antonio Pedro Santos
 * @author     Antonio Pedro Santos <cupidazul@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Port;
use Illuminate\Support\Str;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCellDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSinrDiscovery;
use LibreNMS\OS;
use LibreNMS\OS\Traits\TeldatCellular;

class Teldat extends OS implements
    WirelessCellDiscovery,
    WirelessClientsDiscovery,
    WirelessRssiDiscovery,
    WirelessRsrqDiscovery,
    WirelessRsrpDiscovery,
    WirelessSinrDiscovery
{
    use TeldatCellular;

    private $deviceInfo = [];
    private $hasWLANif = false;
    private $hasGSMif = false;

    public function getdevicePortsInfo()
    {
        $this->deviceInfo = Port::whereIn('device_id', [$this->getDeviceId()])
                ->select('ports.device_id', 'port_id', 'ifAlias', 'ifName', 'ifDescr', 'ifOperStatus')
                ->with(['device' => function ($query) {
                    $query->select('device_id', 'hostname', 'sysName');
                }])->get();

        // Check if any wlan interface is up
        foreach ($this->deviceInfo as $entIndex => $ent) {
            if (Str::startsWith($ent['ifName'], 'wlan') and $ent['ifOperStatus'] == 'up') {
                $this->hasWLANif = true;
                break;
            }
        }

        // Check if any cellullar interface is up
        foreach ($this->deviceInfo as $entIndex => $ent) {
            if (Str::startsWith($ent['ifName'], 'cellular') and $ent['ifOperStatus'] == 'up') {
                $this->hasGSMif = true;
                break;
            }
        }
    }

    /**
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $sensors = [];

        if (empty($this->deviceInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasWLANif) {
            return $sensors;
        }

        $device = $this->getDeviceArray();
        // telProdNpMonInterfWlanBSSCurrent :: Count of current associated stations
        $data = snmpwalk_cache_oid($device, 'telProdNpMonInterfWlanBSSCurrent', [], 'TELDAT-MON-INTERF-WLAN-MIB');

        // Copy deviceInfo Obj
        $ifName = $this->deviceInfo;

        // fixup incorrect/missing IfIndex mapping
        foreach ($data as $index => $_unused) {
            foreach ($ifName as $entIndex => $ent) {
                $descr = $ent['ifName'];
                unset($ifName[$entIndex]); // only use each one once

                if (Str::startsWith($descr, 'wlan')) {
                    // Copy ifName into data
                    $data[$index]['IfIndex'] = $entIndex;
                    $data[$index]['ifName'] = $descr;
                    break;
                }
            }
        }

        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'clients',
                $device['device_id'],
                ".1.3.6.1.4.1.2007.4.1.2.2.2.24.2.1.23.$index",
                'teldat',
                $index,
                $entry['ifName'],
                $entry['telProdNpMonInterfWlanBSSCurrent'],
                1,
                1,
                'sum',
                null,
                40,
                null,
                30,
                $entry['IfIndex'],
                'ports'
            );
        }

        return $sensors;
    }
}
