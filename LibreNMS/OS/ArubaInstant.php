<?php
/**
 * ArubaInstant.php
 *
 * HPE Aruba Instant
 *
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
 * @copyright  2019 Timothy Willey
 * @author     Timothy Willey <developer@timothywilley.net>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Device\Processor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessApCountPolling;
use LibreNMS\Interfaces\Polling\Sensors\WirelessClientsPolling;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\OS;
use LibreNMS\Util\Rewrite;

class ArubaInstant extends OS implements
    OSDiscovery,
    ProcessorDiscovery,
    WirelessApCountDiscovery,
    WirelessApCountPolling,
    WirelessClientsDiscovery,
    WirelessClientsPolling,
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling,
    WirelessNoiseFloorDiscovery,
    WirelessPowerDiscovery,
    WirelessUtilizationDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $device->serial = snmp_getnext($this->getDeviceArray(), 'aiAPSerialNum', '-Oqv', 'AI-AP-MIB');
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $processors = [];
        $ai_mib = 'AI-AP-MIB';
        $ai_ap_data = $this->getCacheTable('aiAccessPointEntry', $ai_mib);

        foreach ($ai_ap_data as $ai_ap => $ai_ap_oid) {
            $value = $ai_ap_oid['aiAPCPUUtilization'];
            $combined_oid = sprintf('%s::%s.%s', $ai_mib, 'aiAPCPUUtilization', Rewrite::oidMac($ai_ap));
            $oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On');
            $description = $ai_ap_data[$ai_ap]['aiAPSerialNum'];
            $processors[] = Processor::discover('aruba-instant', $this->getDeviceId(), $oid, Rewrite::macToHex($ai_ap), $description, 1, $value);
        } // end foreach

        return $processors;
    }

    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $sensors = [];
        $device = $this->getDeviceArray();
        $ai_mib = 'AI-AP-MIB';

        if (intval(explode('.', $device['version'])[0]) >= 8 && intval(explode('.', $device['version'])[1]) >= 4) {
            // version is at least 8.4.0.0
            $ssid_data = $this->getCacheTable('aiWlanSSIDEntry', $ai_mib);

            $ap_data = array_merge_recursive(
                $this->getCacheTable('aiAccessPointEntry', $ai_mib),
                $this->getCacheTable('aiRadioClientNum', $ai_mib)
            );

            $oids = [];
            $total_clients = 0;

            // Clients Per SSID
            foreach ($ssid_data as $index => $entry) {
                $combined_oid = sprintf('%s::%s.%s', $ai_mib, 'aiSSIDClientNum', $index);
                $oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On');
                $description = sprintf('SSID %s Clients', $entry['aiSSID']);
                $oids[] = $oid;
                $total_clients += $entry['aiSSIDClientNum'];
                $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), $oid, 'aruba-instant', $index, $description, $entry['aiSSIDClientNum']);
            }

            // Total Clients across all SSIDs
            $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), $oids, 'aruba-instant', 'total-clients', 'Total Clients', $total_clients);

            // Clients Per Radio
            foreach ($ap_data as $index => $entry) {
                foreach ($entry['aiRadioClientNum'] as $radio => $value) {
                    $combined_oid = sprintf('%s::%s.%s.%s', $ai_mib, 'aiRadioClientNum', Rewrite::oidMac($index), $radio);
                    $oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On');
                    $description = sprintf('%s Radio %s', $entry['aiAPSerialNum'], $radio);
                    $sensor_index = sprintf('%s.%s', Rewrite::macToHex($index), $radio);
                    $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), $oid, 'aruba-instant', $sensor_index, $description, $value);
                }
            }
        } else {
            // version is lower than 8.4.0.0
            // fetch the MAC addresses of currently connected clients, then count them to get an overall total
            $client_data = $this->getCacheTable('aiClientMACAddress', $ai_mib);

            $total_clients = sizeof($client_data);

            $combined_oid = sprintf('%s::%s', $ai_mib, 'aiClientMACAddress');
            $oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On');

            $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), $oid, 'aruba-instant', 'total-clients', 'Total Clients', $total_clients);
        }

        return $sensors;
    }

    /**
     * Discover wireless AP counts. Type is ap-count.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessApCount()
    {
        $sensors = [];
        $ai_mib = 'AI-AP-MIB';
        $ap_data = $this->getCacheTable('aiAPSerialNum', $ai_mib);

        $total_aps = sizeof($ap_data);

        $combined_oid = sprintf('%s::%s', $ai_mib, 'aiAPSerialNum');
        $oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On');

        $sensors[] = new WirelessSensor('ap-count', $this->getDeviceId(), $oid, 'aruba-instant', 'total-aps', 'Total APs', $total_aps);

        return $sensors;
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        // instant
        return $this->discoverInstantRadio('frequency', 'aiRadioChannel');
    }

    /**
     * Discover wireless noise floor. This is in dBm/Hz. Type is noise-floor.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessNoiseFloor()
    {
        // instant
        return $this->discoverInstantRadio('noise-floor', 'aiRadioNoiseFloor');
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        // instant
        return $this->discoverInstantRadio('power', 'aiRadioTransmitPower', '%s Radio %s: Tx Power');
    }

    /**
     * Discover wireless utilization.  This is in %. Type is utilization.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessUtilization()
    {
        // instant
        return $this->discoverInstantRadio('utilization', 'aiRadioUtilization64');
    }

    /**
     * Aruba Instant Radio Discovery
     *
     * @return array Sensors
     */
    private function discoverInstantRadio($type, $mib, $desc = '%s Radio %s')
    {
        $ai_mib = 'AI-AP-MIB';
        $ai_sg_data = array_merge_recursive(
            $this->getCacheTable('aiAPSerialNum', $ai_mib),
            $this->getCacheTable('aiRadioChannel', $ai_mib),
            $this->getCacheTable('aiRadioNoiseFloor', $ai_mib),
            $this->getCacheTable('aiRadioTransmitPower', $ai_mib),
            $this->getCacheTable('aiRadioUtilization64', $ai_mib)
        );

        $sensors = [];

        foreach ($ai_sg_data as $ai_ap => $ai_ap_oid) {
            if (isset($ai_ap_oid[$mib])) {
                foreach ($ai_ap_oid[$mib] as $ai_ap_radio => $value) {
                    $multiplier = 1;
                    if ($type == 'frequency') {
                        $value = WirelessSensor::channelToFrequency($this->decodeChannel($value));
                    }

                    if ($type == 'noise-floor') {
                        $multiplier = -1;
                        $value = $value * $multiplier;
                    }

                    $combined_oid = sprintf('%s::%s.%s.%s', $ai_mib, $mib, Rewrite::oidMac($ai_ap), $ai_ap_radio);
                    $oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On');
                    $description = sprintf($desc, $ai_sg_data[$ai_ap]['aiAPSerialNum'], $ai_ap_radio);
                    $index = sprintf('%s.%s', Rewrite::macToHex($ai_ap), $ai_ap_radio);

                    $sensors[] = new WirelessSensor($type, $this->getDeviceId(), $oid, 'aruba-instant', $index, $description, $value, $multiplier);
                } // end foreach
            } // end if
        } // end foreach

        return $sensors;
    }

    protected function decodeChannel($channel)
    {
        return $channel & 255; // mask off the channel width information
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
        return $this->pollWirelessChannelAsFrequency($sensors, [$this, 'decodeChannel']);
    }

    /**
     * Poll wireless clients
     * The returned array should be sensor_id => value pairs
     *
     * @param array $sensors Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessClients(array $sensors)
    {
        $data = [];
        if (! empty($sensors)) {
            $device = $this->getDeviceArray();

            if (intval(explode('.', $device['version'])[0]) >= 8 && intval(explode('.', $device['version'])[1]) >= 4) {
                // version is at least 8.4.0.0
                $oids = [];

                foreach ($sensors as $sensor) {
                    $oids[$sensor['sensor_id']] = current($sensor['sensor_oids']);
                }

                $snmp_data = snmp_get_multi_oid($this->getDeviceArray(), $oids);

                foreach ($oids as $id => $oid) {
                    $data[$id] = $snmp_data[$oid];
                }
            } else {
                // version is lower than 8.4.0.0
                if (! empty($sensors) && sizeof($sensors) == 1) {
                    $ai_mib = 'AI-AP-MIB';
                    $client_data = $this->getCacheTable('aiClientMACAddress', $ai_mib);

                    if (empty($client_data)) {
                        $total_clients = 0;
                    } else {
                        $total_clients = sizeof($client_data);
                    }

                    $data[$sensors[0]['sensor_id']] = $total_clients;
                }
            }
        }

        return $data;
    }

    /**
     * Poll AP Count
     * The returned array should be sensor_id => value pairs
     *
     * @param array $sensors Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessApCount(array $sensors)
    {
        $data = [];
        if (! empty($sensors) && sizeof($sensors) == 1) {
            $ai_mib = 'AI-AP-MIB';
            $ap_data = $this->getCacheTable('aiAPSerialNum', $ai_mib);

            $total_aps = 0;

            if (! empty($ap_data)) {
                $total_aps = sizeof($ap_data);
            }

            $data[$sensors[0]['sensor_id']] = $total_aps;
        }

        return $data;
    }
}
