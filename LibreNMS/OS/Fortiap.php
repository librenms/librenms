<?php

/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link       https://www.librenms.org
 * @copyright  2023 Transitiv Technologies Ltd. <info@transitiv.co.uk>
 * @author     Adam James <adam.james@transitiv.co.uk>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\OS\Shared\Fortinet;

class Fortiap extends Fortinet implements
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling,
    WirelessPowerDiscovery
{
    public function discoverWirelessClients()
    {
        $fapVapStaInfoCounts = snmpwalk_cache_oid($this->getDeviceArray(), 'fapVapStaInfoCount', [], 'FORTINET-FORTIAP-MIB');
        if (empty($fapVapStaInfoCounts)) {
            return [];
        }
        $fapVapSSIDs = $this->getCacheByIndex('fapVapSSID', 'FORTINET-FORTIAP-MIB');

        $ssids = [];
        foreach ($fapVapStaInfoCounts as $index => $entry) {
            $ssid = $fapVapSSIDs[$index];
            if (empty($ssid)) {
                continue;
            }

            if (isset($ssids[$ssid])) {
                $ssids[$ssid]['oids'][] = '.1.3.6.1.4.1.12356.120.7.1.1.45.' . $index;
                $ssids[$ssid]['count'] += $entry['fapVapStaInfoCount'];
            } else {
                $ssids[$ssid] = [
                    'oids' => ['.1.3.6.1.4.1.12356.120.7.1.1.45.' . $index],
                    'count' => $entry['fapVapStaInfoCount'],
                ];
            }
        }

        $sensors = [];

        foreach ($ssids as $ssid => $data) {
            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $data['oids'],
                'fortiap',
                $ssid,
                'SSID: ' . $ssid,
                $data['count']
            );
        }

        return $sensors;
    }

    public function discoverWirelessFrequency()
    {
        $fapRadioChannelOper = $this->getCacheByIndex('fapRadioChannelOper', 'FORTINET-FORTIAP-MIB');

        $sensors = [];

        foreach ($fapRadioChannelOper as $index => $channel) {
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.12356.120.4.1.1.14.' . $index,
                'fortiap',
                'Radio ' . $index,
                "Frequency (Radio $index)",
                WirelessSensor::channelToFrequency($channel)
            );
        }

        return $sensors;
    }

    public function pollWirelessFrequency(array $sensors)
    {
        return $this->pollWirelessChannelAsFrequency($sensors);
    }

    public function discoverWirelessPower()
    {
        $fapRadioTxPowerOper = $this->getCacheByIndex('fapRadioTxPowerOper', 'FORTINET-FORTIAP-MIB');

        $sensors = [];

        foreach ($fapRadioTxPowerOper as $index => $power) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.12356.120.4.1.1.10.' . $index,
                'fortiap',
                'Radio ' . $index,
                "Tx Power (Radio $index)",
                $power
            );
        }

        return $sensors;
    }
}
