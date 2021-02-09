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
 * @link       http://librenms.org
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\Interfaces\Polling\Sensors\WirelessNoiseFloorPolling;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\OS;

class HiveosWireless extends OS implements
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling,
    WirelessNoiseFloorDiscovery,
    WirelessNoiseFloorPolling,
    WirelessPowerDiscovery,
    OSPolling,
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
        $device = $this->getDeviceArray();

        return [
            Processor::discover(
                $this->getName(),
                $this->getDeviceId(),
                '1.3.6.1.4.1.26928.1.2.3.0', // AH-SYSTEM-MIB::ahCpuUtilization
                0
            ),
        ];
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

        return [
            new WirelessSensor('clients', $this->getDeviceId(), $oid, 'HiveosWireless', 1, 'Clients'),
        ];
    }

    /**
     * Discover wireless frequency.  This is in GHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function pollWirelessFrequency(array $sensors)
    {
        return $this->pollWirelessChannelAsFrequency($sensors);
    }

    public function discoverWirelessFrequency()
    {
        $ahRadioName = $this->getCacheByIndex('ahIfName', 'AH-INTERFACE-MIB');
        $data = snmpwalk_group($this->getDeviceArray(), 'ahRadioChannel', 'AH-INTERFACE-MIB');
        foreach ($data as $index => $frequency) {
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.1.' . $index,
                'hiveos-wireless',
                $index,
                $ahRadioName[$index],
                WirelessSensor::channelToFrequency($frequency['ahRadioChannel'])
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless tx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $sensors = [];

        $ahRadioName = $this->getCacheByIndex('ahIfName', 'AH-INTERFACE-MIB');
        $ahTxPow = snmpwalk_group($this->getDeviceArray(), 'ahRadioTxPower', 'AH-INTERFACE-MIB');
        foreach ($ahTxPow as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.2.' . $index,
                'hiveos-wireless',
                $index,
                'Tx Power: ' . $ahRadioName[$index],
                $entry['ahRadioTxPower']
            );
        }

        return $sensors;
    }

    public function discoverWirelessNoiseFloor()
    {
        $ahRadioName = $this->getCacheByIndex('ahIfName', 'AH-INTERFACE-MIB');
        $ahRadioNoiseFloor = snmpwalk_group($this->getDeviceArray(), 'ahRadioNoiseFloor', 'AH-INTERFACE-MIB');
        $sensors = [];
        foreach ($ahRadioNoiseFloor as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'noise-floor',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.26928.1.1.1.2.1.5.1.3.' . $index,
                'hiveos-wireless',
                $index,
                'Noise floor ' . $ahRadioName[$index],
                $entry['ahRadioNoiseFloor'] - 256
            );
        }

        return $sensors;
    }

    /**
     * Poll wireless noise floor
     * The returned array should be sensor_id => value pairs
     *
     * @param array $sensors Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessNoiseFloor(array $sensors)
    {
        $data = [];
        $ahRadioNoiseFloor = snmpwalk_group($this->getDeviceArray(), 'ahRadioNoiseFloor', 'AH-INTERFACE-MIB');
        foreach ($sensors as $sensor) {
            $data[$sensor['sensor_id']] = $ahRadioNoiseFloor[$sensor['sensor_index']]['ahRadioNoiseFloor'] - 256;
        }

        return $data;
    }

    /**Poll ahRadioTxAirtime and ahRadioRxAirtime and graph deltas
     * 
     */
    public function pollOS()
    {
        $wifi0txairtime = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.26928.1.1.1.2.1.3.1.22.7', '-Ovq');
        $wifi0rxairtime = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.26928.1.1.1.2.1.3.1.23.7', '-Ovq');
        if (is_numeric($wifi0txairtime)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('wifi0txairtime', 'COUNTER', 0)
                ->addDataset('wifi0rxairtime', 'COUNTER', 0);

            echo "TX Airtime: $wifi0txairtime\n RX Airtime: $wifi0rxairtime\n";
            $fields = [
                'wifi0txairtime' => $wifi0txairtime,
                'wifi0rxairtime' => $wifi0rxairtime,
            ];

            $tags = compact('rrd_def');
            app()->make('Datastore')->put($this->getDeviceArray(), 'ahradio_wifi0_airtime', $tags, $fields);
            $this->enableGraph('ahradio_wifi0_airtime');
        }

        $wifi1txairtime = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.26928.1.1.1.2.1.3.1.22.8', '-Ovq');
        $wifi1rxairtime = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.26928.1.1.1.2.1.3.1.23.8', '-Ovq');
        if (is_numeric($wifi1txairtime)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('wifi1txairtime', 'COUNTER', 0)
                ->addDataset('wifi1rxairtime', 'COUNTER', 0);

            echo "TX Airtime: $wifi1txairtime\n RX Airtime: $wifi1rxairtime\n";
            $fields = [
                'wifi1txairtime' => $wifi1txairtime,
                'wifi1rxairtime' => $wifi1rxairtime,
            ];

            $tags = compact('rrd_def');
            app()->make('Datastore')->put($this->getDeviceArray(), 'ahradio_wifi1_airtime', $tags, $fields);
            $this->enableGraph('ahradio_wifi1_airtime');
        }
    }
}
