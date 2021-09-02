<?php
/**
 * Pepwave.php
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
 * @copyright  2020 Jozef Rebjak
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSinrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class Pepwave extends OS implements
    WirelessClientsDiscovery,
    WirelessSnrDiscovery,
    WirelessRsrpDiscovery,
    WirelessRsrqDiscovery,
    WirelessRssiDiscovery,
    WirelessSinrDiscovery
{
    public function discoverWirelessClients()
    {
        $oid = '.1.3.6.1.4.1.27662.4.1.1.7.0';

        return [
            new WirelessSensor('clients', $this->getDeviceId(), $oid, 'pepwave', 1, 'Online APs'),
        ];
    }

    public function discoverWirelessRssi()
    {
        $data = snmpwalk_group($this->getDeviceArray(), 'cellularSignalRssi', 'CELLULAR');
        $sensors = [];
        foreach ($data as $index => $rssi_value) {
            if ($rssi_value['cellularSignalRssi'] != '-9999') {
                $sensors[] = new WirelessSensor('rssi', $this->getDeviceId(), '.1.3.6.1.4.1.23695.200.1.12.1.1.1.3.' . $index, 'pepwave', 'cellularSignalRssi' . $index, 'Celullar ' . ($index + 1), $rssi_value['cellularSignalRssi'], 1, 1);
            }
        }

        return $sensors;
    }

    public function discoverWirelessSnr()
    {
        $data = snmpwalk_group($this->getDeviceArray(), 'cellularSignalSnr', 'CELLULAR');
        $sensors = [];
        foreach ($data as $index => $snr_value) {
            if ($snr_value['cellularSignalSnr'] != '-9999') {
                $sensors[] = new WirelessSensor('snr', $this->getDeviceId(), '.1.3.6.1.4.1.23695.200.1.12.1.1.1.4.' . $index, 'pepwave', 'cellularSignalSnr' . $index, 'Celullar ' . ($index + 1), $snr_value['cellularSignalSnr'], 1, 1);
            }
        }

        return $sensors;
    }

    public function discoverWirelessSinr()
    {
        $data = snmpwalk_group($this->getDeviceArray(), 'cellularSignalSinr', 'CELLULAR');
        $sensors = [];
        foreach ($data as $index => $sinr_value) {
            if ($sinr_value['cellularSignalSinr'] != '-9999') {
                $sensors[] = new WirelessSensor('sinr', $this->getDeviceId(), '.1.3.6.1.4.1.23695.200.1.12.1.1.1.5.' . $index, 'pepwave', 'cellularSignalSinr' . $index, 'Celullar ' . ($index + 1), $sinr_value['cellularSignalSinr'], 1, 1);
            }
        }

        return $sensors;
    }

    public function discoverWirelessRsrp()
    {
        $data = snmpwalk_group($this->getDeviceArray(), 'cellularSignalRsrp', 'CELLULAR');
        $sensors = [];
        foreach ($data as $index => $rsrp_value) {
            if ($rsrp_value['cellularSignalRsrp'] != '-9999') {
                $sensors[] = new WirelessSensor('rsrp', $this->getDeviceId(), '.1.3.6.1.4.1.23695.200.1.12.1.1.1.7.' . $index, 'pepwave', 'cellularSignalRsrp' . $index, 'Celullar ' . ($index + 1), $rsrp_value['cellularSignalRsrp'], 1, 1);
            }
        }

        return $sensors;
    }

    public function discoverWirelessRsrq()
    {
        $data = snmpwalk_group($this->getDeviceArray(), 'cellularSignalRsrq', 'CELLULAR');
        $sensors = [];
        foreach ($data as $index => $rsrq_value) {
            if ($rsrq_value['cellularSignalRsrq'] != '-9999') {
                $sensors[] = new WirelessSensor('rsrq', $this->getDeviceId(), '.1.3.6.1.4.1.23695.200.1.12.1.1.1.8.' . $index, 'pepwave', 'cellularSignalRsrq' . $index, 'Celullar ' . ($index + 1), $rsrq_value['cellularSignalRsrq'], 1, 1);
            }
        }

        return $sensors;
    }
}
