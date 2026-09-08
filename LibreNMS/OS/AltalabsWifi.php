<?php

/**
 * AltalabsWifi.php
 *
 * Alta Labs Wireless APs
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
 * @copyright  2026 Alta Labs
 * @author     Chris Buechler <chris@alta.inc>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\OS;

class AltalabsWifi extends OS implements
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling,
    WirelessUtilizationDiscovery
{
    private const MIB = 'ALTA-WIRELESS-MIB';
    private const OID_WLAN_RADIO_CHANNEL = '.1.3.6.1.4.1.61802.1.1.1.1.4.';
    private const OID_WLAN_RADIO_UTILIZATION = '.1.3.6.1.4.1.61802.1.1.1.1.5.';
    private const OID_WLAN_VAP_STA_COUNT = '.1.3.6.1.4.1.61802.1.1.2.1.9.';

    /**
     * Returns an array of LibreNMS\Device\Sensor objects
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $client_oids = snmpwalk_cache_oid($this->getDeviceArray(), 'wlanVapStaCount', [], self::MIB);
        if (empty($client_oids)) {
            return [];
        }
        $vap_radios = $this->getCacheByIndex('wlanVapBand', self::MIB);
        $ssid_ids = $this->getCacheByIndex('wlanVapSsid', self::MIB);

        $radios = [];
        foreach ($client_oids as $index => $entry) {
            if (! is_numeric($entry['wlanVapStaCount'] ?? null)) {
                continue;
            }

            $radio_name = $this->formatBand($vap_radios[$index] ?? null);
            $radios[$radio_name]['oids'][] = self::OID_WLAN_VAP_STA_COUNT . $index;
            if (isset($radios[$radio_name]['count'])) {
                $radios[$radio_name]['count'] += $entry['wlanVapStaCount'];
            } else {
                $radios[$radio_name]['count'] = $entry['wlanVapStaCount'];
            }
        }

        $sensors = [];

        // discover client counts by radio
        foreach ($radios as $name => $data) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Clients,
                $this->getDeviceId(),
                $data['oids'],
                'altalabs-wifi',
                $name,
                "Clients ({$name})",
                $data['count']
            );
        }

        // discover client counts by SSID
        $ssids = [];
        foreach ($client_oids as $index => $entry) {
            if (! is_numeric($entry['wlanVapStaCount'] ?? null)) {
                continue;
            }

            $ssid = $ssid_ids[$index] ?? null;
            if (! empty($ssid)) {
                if (isset($ssids[$ssid])) {
                    // .1.3.6.1.4.1.61802.1.1.2.1.9 = wlanVapStaCount
                    $ssids[$ssid]['oids'][] = self::OID_WLAN_VAP_STA_COUNT . $index;
                    $ssids[$ssid]['count'] += $entry['wlanVapStaCount'];
                } else {
                    $ssids[$ssid] = [
                        'oids' => [self::OID_WLAN_VAP_STA_COUNT . $index],
                        'count' => $entry['wlanVapStaCount'],
                    ];
                }
            }
        }

        foreach ($ssids as $ssid => $data) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Clients,
                $this->getDeviceId(),
                $data['oids'],
                'altalabs-wifi',
                $ssid,
                'SSID: ' . $ssid,
                $data['count']
            );
        }

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
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'wlanRadioChannel', [], self::MIB);
        $radio_bands = $this->getCacheByIndex('wlanRadioBand', self::MIB);
        $radio_names = $this->getCacheByIndex('wlanRadioIfname', self::MIB);

        $sensors = [];
        foreach ($data as $index => $entry) {
            if (! is_numeric($entry['wlanRadioChannel'] ?? null)) {
                continue;
            }

            $band = $radio_bands[$index] ?? null;
            $radio = $this->formatBand($band);
            $description = isset($radio_names[$index]) ? "{$radio_names[$index]} ({$radio})" : "Frequency ($radio)";
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Frequency,
                $this->getDeviceId(),
                self::OID_WLAN_RADIO_CHANNEL . $index,
                'altalabs-wifi',
                $index,
                $description,
                $this->channelToFrequency($entry['wlanRadioChannel'], $band)
            );
        }

        return $sensors;
    }

    /**
     * Poll wireless frequency as MHz
     * The returned array should be sensor_id => value pairs
     *
     * @param  array  $sensors  Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessFrequency(array $sensors)
    {
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'wlanRadioChannel', [], self::MIB);
        $radio_bands = $this->getCacheByIndex('wlanRadioBand', self::MIB);

        $polled = [];
        foreach ($sensors as $sensor) {
            $index = $sensor['sensor_index'];
            if (is_numeric($data[$index]['wlanRadioChannel'] ?? null)) {
                $polled[$sensor['sensor_id']] = $this->channelToFrequency($data[$index]['wlanRadioChannel'], $radio_bands[$index] ?? null);
            }
        }

        return $polled;
    }

    /**
     * Discover wireless utilization.  This is in %. Type is utilization.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessUtilization()
    {
        $util_oids = snmpwalk_cache_oid($this->getDeviceArray(), 'wlanRadioChanUtilization', [], self::MIB);
        if (empty($util_oids)) {
            return [];
        }
        $radio_names = $this->getCacheByIndex('wlanRadioBand', self::MIB);

        $sensors = [];
        foreach ($util_oids as $index => $entry) {
            if (! is_numeric($entry['wlanRadioChanUtilization'] ?? null)) {
                continue;
            }

            $name = $this->formatBand($radio_names[$index] ?? null);
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Utilization,
                $this->getDeviceId(),
                self::OID_WLAN_RADIO_UTILIZATION . $index,
                'altalabs-wifi',
                $index,
                "Channel Utilization ({$name})",
                $entry['wlanRadioChanUtilization']
            );
        }

        return $sensors;
    }

    private function formatBand($band): string
    {
        return match ((int) $band) {
            2 => '2.4G',
            5 => '5G',
            6 => '6G',
            default => is_numeric($band) ? "{$band}G" : 'unknown',
        };
    }

    private function channelToFrequency($channel, $band): int
    {
        if ((int) $band === 6) {
            return 5950 + ((int) $channel * 5);
        }

        return WirelessSensor::channelToFrequency($channel);
    }
}
