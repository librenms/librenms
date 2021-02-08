<?php
/**
 * XirrusAos.php
 *
 * Xirrus AOS OS
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\OS;

class XirrusAos extends OS implements
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling,
    WirelessNoiseFloorDiscovery,
    WirelessUtilizationDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery
{
    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $oid = '.1.3.6.1.4.1.21013.1.2.12.1.2.22.0'; // XIRRUS-MIB::globalNumStations.0

        return [
            new WirelessSensor('clients', $this->getDeviceId(), $oid, 'xirrus', 0, 'Clients'),
        ];
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        return $this->discoverSensor('frequency', 'realtimeMonitorChannel', '.1.3.6.1.4.1.21013.1.2.24.7.1.3.');
    }

    /**
     * Poll wireless frequency as MHz
     * The returned array should be sensor_id => value pairs
     *
     * @param array $sensors Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessFrequency(array $sensors)
    {
        return $this->pollWirelessChannelAsFrequency($sensors);
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessNoiseFloor()
    {
        return $this->discoverSensor('noise-floor', 'realtimeMonitorNoiseFloor', '.1.3.6.1.4.1.21013.1.2.24.7.1.10.');
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        return $this->discoverSensor('rate', 'realtimeMonitorAverageDataRate', '.1.3.6.1.4.1.21013.1.2.24.7.1.7.');
    }

    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        return $this->discoverSensor('rssi', 'realtimeMonitorAverageRSSI', '.1.3.6.1.4.1.21013.1.2.24.7.1.8.');
    }

    /**
     * Discover wireless SNR.  This is in dB. Type is snr.
     * Formula: SNR = Signal or Rx Power - Noise Floor
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSnr()
    {
        return $this->discoverSensor('snr', 'realtimeMonitorSignalToNoiseRatio', '.1.3.6.1.4.1.21013.1.2.24.7.1.9.');
    }

    /**
     * Discover wireless utilization.  This is in %. Type is utilization.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessUtilization()
    {
        return $this->discoverSensor('utilization', 'realtimeMonitorDot11Busy', '.1.3.6.1.4.1.21013.1.2.24.7.1.11.');
    }

    private function discoverSensor($type, $oid, $oid_num_prefix)
    {
        $names = $this->getCacheByIndex('realtimeMonitorIfaceName', 'XIRRUS-MIB');
        $nf = snmpwalk_cache_oid($this->getDeviceArray(), $oid, [], 'XIRRUS-MIB');

        $sensors = [];
        foreach ($nf as $index => $entry) {
            $sensors[] = new WirelessSensor(
                $type,
                $this->getDeviceId(),
                $oid_num_prefix . $index,
                'xirrus',
                $index,
                $names[$index],
                $type == 'frequency' ? WirelessSensor::channelToFrequency($entry[$oid]) : $entry[$oid]
            );
        }

        return $sensors;
    }
}
