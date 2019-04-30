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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Timothy Willey
 * @author     Timothy Willey <developer@timothywilley.net>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\OS;
use LibreNMS\Util\Rewrite;

class ArubaInstant extends OS implements
    ProcessorDiscovery,
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling,
    WirelessNoiseFloorDiscovery,
    WirelessPowerDiscovery,
    WirelessUtilizationDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        // instant
        return $this->discoverInstantCPU('aiAPCPUUtilization');
    }


    /**
     * Aruba Instant CPU Discovery
     *
     * @return array Sensors
     */
    private function discoverInstantCPU($mib)
    {
        $ai_mib = 'AI-AP-MIB';
        $ai_sg_data = $this->getCacheTable('aiStateGroup', $ai_mib);
        $processors = [];
        foreach ($ai_sg_data as $ai_ap => $ai_ap_oid) {
            $value = $ai_ap_oid[$mib];
            $combined_oid = sprintf('%s::%s.%s', $ai_mib, $mib, Rewrite::oidMac($ai_ap));
            $oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On', null);
            $description = $ai_sg_data[$ai_ap]['aiAPSerialNum'];
            $processors[] = Processor::discover('aruba-instant', $this->getDeviceId(), $oid, Rewrite::macToHex($ai_ap), $description, 1, $value);
            d_echo('Processor Array:'.PHP_EOL);
            d_echo($processors);
        }
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
        return $this->discoverInstantRadio('clients', 'aiRadioClientNum');
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
        return $this->discoverInstantRadio('power', 'aiRadioTransmitPower', "%s Radio %s: Tx Power");
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
        $ai_sg_data = $this->getCacheTable('aiStateGroup', $ai_mib);

        $sensors = [];

        if ($type == 'clients') {
            // clients per ssid
            $device = $this->getDevice();
            $ssid_data = $this->getCacheTable('AiWlanSSIDEntry', $ai_mib);
            $ssid_name_mib = 'aiSSID';

            d_echo('$device: '.var_export($device, 1).PHP_EOL);
            d_echo('$ssid_data: '.var_export($ssid_data, 1).PHP_EOL);

            $oids = array();
            $total_clients = 0;
            if (intval(explode('.', $device['version'])[0]) >= 8 && intval(explode('.', $device['version'])[1]) >= 4) {
                // version >= 8.4.0.0
                $ssid_clients_mib = 'aiSSIDClientNum';

                foreach ($ssid_data as $index => $entry) {
                    $combined_oid = sprintf('%s::%s.%s', $ai_mib, $ssid_clients_mib, $index);
                    $oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On', null);
                    $description = sprintf('SSID %s Clients', $entry[$ssid_name_mib]);
                    $oids[] = $oid;
                    $total_clients += $entry[$ssid_clients_mib];
                    $sensors[] = new WirelessSensor($type, $this->getDeviceId(), $oid, 'aruba-instant', $index, $description, $entry[$ssid_clients_mib]);
                }

                $sensors[] = new WirelessSensor($type, $this->getDeviceId(), $oids, 'aruba-instant', null, 'Total Clients', $total_clients);
            } else {
                // version < 8.4.0.0
                // count the number of clients per ssid and create a sensor...
            }
        } // end if

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
                    $oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On', null);
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
}
