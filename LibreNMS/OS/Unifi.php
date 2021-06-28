<?php
/**
 * Unifi.php
 *
 * Ubiquiti Unifi
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

use App\Models\Device;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCcqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessCcqPolling;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\OS;

class Unifi extends OS implements
    ProcessorDiscovery,
    WirelessClientsDiscovery,
    WirelessCcqDiscovery,
    WirelessCcqPolling,
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling,
    WirelessPowerDiscovery,
    WirelessUtilizationDiscovery
{
    use OS\Traits\FrogfootResources {
        OS\Traits\FrogfootResources::discoverProcessors as discoverFrogfootProcessors;
    }

    private $ccqDivisor = 10;

    public function discoverOS(Device $device): void
    {
        // try the Unifi MIB first, then fall back to dot11manufacturer
        if ($data = snmp_getnext_multi($this->getDeviceArray(), ['unifiApSystemModel', 'unifiApSystemVersion'], '-OQUs', 'UBNT-UniFi-MIB')) {
            $device->hardware = $data['unifiApSystemModel'] ?? $device->hardware;
            $device->version = $data['unifiApSystemVersion'] ?? $device->version;
        } elseif ($data = snmp_getnext_multi($this->getDeviceArray(), ['dot11manufacturerProductName', 'dot11manufacturerProductVersion'], '-OQUs', 'IEEE802dot11-MIB')) {
            $device->hardware = $data['dot11manufacturerProductName'] ?? $device->hardware;
            if (preg_match('/(v[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $data['dot11manufacturerProductVersion'], $matches)) {
                $device->version = $matches[1];
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
        return $this->discoverHrProcessors() ?: $this->discoverFrogfootProcessors();
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $client_oids = snmpwalk_cache_oid($this->getDeviceArray(), 'unifiVapNumStations', [], 'UBNT-UniFi-MIB');
        if (empty($client_oids)) {
            return [];
        }
        $vap_radios = $this->getCacheByIndex('unifiVapRadio', 'UBNT-UniFi-MIB');
        $ssid_ids = $this->getCacheByIndex('unifiVapEssId', 'UBNT-UniFi-MIB');

        $radios = [];
        foreach ($client_oids as $index => $entry) {
            $radio_name = $vap_radios[$index];
            $radios[$radio_name]['oids'][] = '.1.3.6.1.4.1.41112.1.6.1.2.1.8.' . $index;
            if (isset($radios[$radio_name]['count'])) {
                $radios[$radio_name]['count'] += $entry['unifiVapNumStations'];
            } else {
                $radios[$radio_name]['count'] = $entry['unifiVapNumStations'];
            }
        }

        $sensors = [];

        // discover client counts by radio
        foreach ($radios as $name => $data) {
            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $data['oids'],
                'unifi',
                $name,
                "Clients ($name)",
                $data['count'],
                1,
                1,
                'sum',
                null,
                40,
                null,
                30
            );
        }

        // discover client counts by SSID
        $ssids = [];
        foreach ($client_oids as $index => $entry) {
            $ssid = $ssid_ids[$index];
            if (! empty($ssid)) {
                if (isset($ssids[$ssid])) {
                    $ssids[$ssid]['oids'][] = '.1.3.6.1.4.1.41112.1.6.1.2.1.8.' . $index;
                    $ssids[$ssid]['count'] += $entry['unifiVapNumStations'];
                } else {
                    $ssids[$ssid] = [
                        'oids' => ['.1.3.6.1.4.1.41112.1.6.1.2.1.8.' . $index],
                        'count' => $entry['unifiVapNumStations'],
                    ];
                }
            }
        }

        foreach ($ssids as $ssid => $data) {
            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $data['oids'],
                'unifi',
                $ssid,
                'SSID: ' . $ssid,
                $data['count']
            );
        }

        return $sensors;
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessCcq()
    {
        $ccq_oids = snmpwalk_cache_oid($this->getDeviceArray(), 'unifiVapCcq', [], 'UBNT-UniFi-MIB');
        if (empty($ccq_oids)) {
            return [];
        }
        $vap_radios = $this->getCacheByIndex('unifiVapRadio', 'UBNT-UniFi-MIB');
        $ssids = $this->getCacheByIndex('unifiVapEssId', 'UBNT-UniFi-MIB');

        $sensors = [];
        foreach ($ccq_oids as $index => $entry) {
            if ($ssids[$index]) { // don't discover ssids with empty names
                $sensors[] = new WirelessSensor(
                    'ccq',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.41112.1.6.1.2.1.3.' . $index,
                    'unifi',
                    $index,
                    "SSID: {$ssids[$index]} ({$vap_radios[$index]})",
                    min($entry['unifiVapCcq'] / $this->ccqDivisor, 100),
                    1,
                    $this->ccqDivisor
                );
            }
        }

        return $sensors;
    }

    /**
     * Poll wireless client connection quality
     * The returned array should be sensor_id => value pairs
     *
     * @param array $sensors Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessCcq(array $sensors)
    {
        if (empty($sensors)) {
            return [];
        }

        $ccq_oids = snmpwalk_cache_oid($this->getDeviceArray(), 'unifiVapCcq', [], 'UBNT-UniFi-MIB');

        $data = [];
        foreach ($sensors as $sensor) {
            $index = $sensor['sensor_index'];
            $data[$sensor['sensor_id']] = min($ccq_oids[$index]['unifiVapCcq'] / $this->ccqDivisor, 100);
        }

        return $data;
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'unifiVapChannel', [], 'UBNT-UniFi-MIB');
        $vap_radios = $this->getCacheByIndex('unifiVapRadio', 'UBNT-UniFi-MIB');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $radio = $vap_radios[$index];
            if (isset($sensors[$radio])) {
                continue;
            }
            $sensors[$radio] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.41112.1.6.1.2.1.4.' . $index,
                'unifi',
                $radio,
                "Frequency ($radio)",
                WirelessSensor::channelToFrequency($entry['unifiVapChannel'])
            );
        }

        return $sensors;
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
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $power_oids = snmpwalk_cache_oid($this->getDeviceArray(), 'unifiVapTxPower', [], 'UBNT-UniFi-MIB');
        if (empty($power_oids)) {
            return [];
        }
        $vap_radios = $this->getCacheByIndex('unifiVapRadio', 'UBNT-UniFi-MIB');

        // pick one oid for each radio, hopefully ssids don't change... not sure why this is supplied by vap
        $sensors = [];
        foreach ($power_oids as $index => $entry) {
            $radio_name = $vap_radios[$index];
            if (! isset($sensors[$radio_name])) {
                $sensors[$radio_name] = new WirelessSensor(
                    'power',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.41112.1.6.1.2.1.21.' . $index,
                    'unifi-tx',
                    $radio_name,
                    "Tx Power ($radio_name)",
                    $entry['unifiVapTxPower']
                );
            }
        }

        return $sensors;
    }

    /**
     * Discover wireless utilization.  This is in %. Type is utilization.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessUtilization()
    {
        $util_oids = snmpwalk_cache_oid($this->getDeviceArray(), 'unifiRadioCuTotal', [], 'UBNT-UniFi-MIB');
        if (empty($util_oids)) {
            return [];
        }
        $util_oids = snmpwalk_cache_oid($this->getDeviceArray(), 'unifiRadioCuSelfRx', $util_oids, 'UBNT-UniFi-MIB');
        $util_oids = snmpwalk_cache_oid($this->getDeviceArray(), 'unifiRadioCuSelfTx', $util_oids, 'UBNT-UniFi-MIB');
        $util_oids = snmpwalk_cache_oid($this->getDeviceArray(), 'unifiRadioOtherBss', $util_oids, 'UBNT-UniFi-MIB');
        $radio_names = $this->getCacheByIndex('unifiRadioRadio', 'UBNT-UniFi-MIB');

        $sensors = [];
        foreach ($radio_names as $index => $name) {
            $sensors[] = new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.41112.1.6.1.1.1.6.' . $index,
                'unifi-total',
                $index,
                "Total Util ($name)",
                $util_oids[$index]['unifiRadioCuTotal']
            );
            $sensors[] = new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.41112.1.6.1.1.1.7.' . $index,
                'unifi-rx',
                $index,
                "Self Rx Util ($name)",
                $util_oids[$index]['unifiRadioCuSelfRx']
            );
            $sensors[] = new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.41112.1.6.1.1.1.8.' . $index,
                'unifi-tx',
                $index,
                "Self Tx Util ($name)",
                $util_oids[$index]['unifiRadioCuSelfTx']
            );
            $sensors[] = new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.41112.1.6.1.1.1.9.' . $index,
                'unifi-other',
                $index,
                "Other BSS Util ($name)",
                $util_oids[$index]['unifiRadioOtherBss']
            );
        }

        return $sensors;
    }
}
