<?php
/**
 * HiveosWireless.php
 *
 * AeroHive Hiveos-Wireless
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
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class HiveosWireless extends OS implements
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
     	$device = $this->getDevice();
        return array(
            Processor::discover(
                $this->getName(),
                $this->getDeviceId(),
                '1.3.6.1.4.1.26928.1.2.3.0', // AH-SYSTEM-MIB::ahCpuUtilization
                0
            )
        );
    }

    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $oid = '.1.3.6.1.4.1.26928.1.2.9.0'; // AH-SYSTEM-MIB::ahClientCount
        return array(
            new WirelessSensor('clients', $this->getDeviceId(), $oid, 'HiveosWireless', 1, 'Clients')
        );
    }

    /**
     * Discover wireless frequency.  This is in GHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */

    /**
    public function discoverWirelessFrequency()
    {
    $data = snmp_get_multi_oid($device, '.1.3.6.1.4.1.26928.1.1.1.2.1.1.1.5 .1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.7 .1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.8 .1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.9 .1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.10', '-OUQn');
    $apmodel = '.1.3.6.1.4.1.26928.1.1.1.2.1.1.1.5';
//    $wifi0 = isset($data['.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.7']) ? $data['.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.7'] : '';
//    $wifi1 = isset($data['.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.8']) ? $data['.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.8'] : '';
    //$wifi0 = isset($data['.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.9']) ? $data['.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.9'] : '';
    //$wifi1 = isset($data['.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.10']) ? $data['.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.10'] : '';
    //if ($apmodel == 'AP250') {
    $wifi0 = '.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.7';
    $wifi1 = '.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.8';
    //}
    return array(
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $wifi0,
                'wifi0',
                1,
                'Wifi0 Frequency'
            ),
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $wifi1,
                'wifi1',
                1,
                'Wifi1 Frequency'
            ),
        );
    }
    */    


 public function discoverWirelessFrequency()
    {
        $oid = '.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1';
        $data = snmpwalk_group($this->getDevice(), $oid);
        $sensors = array();
        foreach ($data as $index => $entry) {
            $radio = $data[$index];
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                //'.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.' . $index,
                $index,
                'hiveoswireless',
                $entry,
                "wlan[$index]",
                WirelessSensor::channelToFrequency($entry['$index'])
            );
        }
        return $sensors;
    }

}
