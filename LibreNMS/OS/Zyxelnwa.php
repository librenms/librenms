<?php

/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2017 Thomas GAGNIERE
 * @author     Thomas GAGNIERE <tgagniere@reseau-concept.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\OS\Shared\Zyxel;

class Zyxelnwa extends Zyxel implements OSDiscovery, WirelessClientsDiscovery, WirelessFrequencyDiscovery, WirelessFrequencyPolling
{
    public function discoverWirelessClients()
    {
        $sensors = [];
        $base_oid = '.1.3.6.1.4.1.890.1.15.3.5.1.1.2.'; // ZYXEL-ES-WIRELESS::wlanStationCount

        foreach ($this->getWlanRadioTable() as $index => $row) {
            $radio = $this->getRadioName($row['ZYXEL-ES-WIRELESS::wlanMode']);
            $sensors[] = new WirelessSensor(WirelessSensorType::Clients, $this->getDeviceId(), $base_oid . $index, 'zyxelnwa', $index, $radio, $row['ZYXEL-ES-WIRELESS::wlanStationCount']);
        }

        $total = \SnmpQuery::get('ZYXEL-ES-WIRELESS::wlanTotalStationCount.0')->value();
        if ($total !== '') {
            $sensors[] = new WirelessSensor(WirelessSensorType::Clients, $this->getDeviceId(), '.1.3.6.1.4.1.890.1.15.3.5.15.0', 'zyxelnwa', 'total', 'Total', (int) $total);
        }

        return $sensors;
    }

    public function discoverWirelessFrequency()
    {
        $sensors = [];
        $base_oid = '.1.3.6.1.4.1.890.1.15.3.5.1.1.1.'; // ZYXEL-ES-WIRELESS::wlanCurrentChannel

        foreach ($this->getWlanRadioTable() as $index => $row) {
            $mode = (string) $row['ZYXEL-ES-WIRELESS::wlanMode'];
            $channel = (int) $row['ZYXEL-ES-WIRELESS::wlanCurrentChannel'];
            $radio = $this->getRadioName($mode);
            $frequency = $this->channelToFrequency($channel, $mode);

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Frequency,
                $this->getDeviceId(),
                $base_oid . $index,
                'zyxelnwa',
                $index,
                $radio,
                $frequency
            );
        }

        return $sensors;
    }

    public function pollWirelessFrequency(array $sensors)
    {
        if (empty($sensors)) {
            return [];
        }

        $radioTable = $this->getWlanRadioTable();
        $data = [];

        foreach ($sensors as $sensor) {
            $index = $sensor['sensor_index'];

            if (isset($radioTable[$index])) {
                $mode = (string) $radioTable[$index]['ZYXEL-ES-WIRELESS::wlanMode'];
                $channel = (int) $radioTable[$index]['ZYXEL-ES-WIRELESS::wlanCurrentChannel'];

                $data[$sensor['sensor_id']] = $this->channelToFrequency($channel, $mode);
            }
        }

        return $data;
    }

    private function channelToFrequency(int $channel, string $mode): int
    {
        if ($mode === '3') {
            // 6 GHz channel 1 is 5955 MHz with 5 MHz channel spacing.
            return 5950 + ($channel * 5);
        }

        return WirelessSensor::channelToFrequency($channel);
    }

    private function getRadioName($value): string
    {
        return match ($value) {
            '1' => '2.4GHz',
            '2' => '5GHz',
            '3' => '6GHz',
            default => 'Unknown',
        };
    }

    private function getWlanRadioTable()
    {
        return \SnmpQuery::cache()
            ->walk('ZYXEL-ES-WIRELESS::wlanRadioTable')
            ->table(1);
    }
}
