<?php

/**
 * AltalabsAp.php
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
use SnmpQuery;

class AltalabsAp extends OS implements
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling,
    WirelessUtilizationDiscovery
{
    public function discoverWirelessClients()
    {
        $vaps = SnmpQuery::hideMib()->walk([
            'ALTA-WIRELESS-MIB::wlanVapStaCount',
            'ALTA-WIRELESS-MIB::wlanVapBand',
            'ALTA-WIRELESS-MIB::wlanVapSsid',
        ])->table(1);

        $radios = [];
        $ssids = [];
        foreach ($vaps as $index => $vap) {
            if (! is_numeric($vap['wlanVapStaCount'] ?? null)) {
                continue;
            }

            $oid = '.1.3.6.1.4.1.61802.1.1.2.1.9.' . $index;
            $count = (int) $vap['wlanVapStaCount'];

            $radio = $this->formatBand($vap['wlanVapBand'] ?? null);
            $radios[$radio]['oids'][] = $oid;
            $radios[$radio]['count'] = ($radios[$radio]['count'] ?? 0) + $count;

            $ssid = $vap['wlanVapSsid'] ?? null;
            if (! empty($ssid)) {
                $ssids[$ssid]['oids'][] = $oid;
                $ssids[$ssid]['count'] = ($ssids[$ssid]['count'] ?? 0) + $count;
            }
        }

        $sensors = [];
        foreach ($radios as $name => $data) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Clients,
                $this->getDeviceId(),
                $data['oids'],
                'altalabs-wifi',
                $name,
                "Clients ($name)",
                $data['count']
            );
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

    public function discoverWirelessFrequency()
    {
        $sensors = [];
        foreach ($this->getRadios() as $index => $radio) {
            if (! is_numeric($radio['wlanRadioChannel'] ?? null)) {
                continue;
            }

            $band = $radio['wlanRadioBand'] ?? null;
            $name = $this->formatBand($band);
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Frequency,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.61802.1.1.1.1.4.' . $index,
                'altalabs-wifi',
                $index,
                isset($radio['wlanRadioIfname']) ? "{$radio['wlanRadioIfname']} ($name)" : "Frequency ($name)",
                $this->channelToFrequency($radio['wlanRadioChannel'], $band)
            );
        }

        return $sensors;
    }

    public function pollWirelessFrequency(array $sensors)
    {
        $radios = $this->getRadios();

        $polled = [];
        foreach ($sensors as $sensor) {
            $radio = $radios[$sensor['sensor_index']] ?? [];
            if (is_numeric($radio['wlanRadioChannel'] ?? null)) {
                $polled[$sensor['sensor_id']] = $this->channelToFrequency($radio['wlanRadioChannel'], $radio['wlanRadioBand'] ?? null);
            }
        }

        return $polled;
    }

    public function discoverWirelessUtilization()
    {
        $sensors = [];
        foreach ($this->getRadios() as $index => $radio) {
            if (! is_numeric($radio['wlanRadioChanUtilization'] ?? null)) {
                continue;
            }

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Utilization,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.61802.1.1.1.1.5.' . $index,
                'altalabs-wifi',
                $index,
                'Channel Utilization (' . $this->formatBand($radio['wlanRadioBand'] ?? null) . ')',
                $radio['wlanRadioChanUtilization']
            );
        }

        return $sensors;
    }

    private function getRadios(): array
    {
        return SnmpQuery::cache()->hideMib()->walk([
            'ALTA-WIRELESS-MIB::wlanRadioChannel',
            'ALTA-WIRELESS-MIB::wlanRadioBand',
            'ALTA-WIRELESS-MIB::wlanRadioIfname',
            'ALTA-WIRELESS-MIB::wlanRadioChanUtilization',
        ])->table(1);
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
