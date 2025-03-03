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

use App\Models\Device;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\OS\Shared\Fortinet;

class Fortiap extends Fortinet implements
    OSDiscovery,
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling,
    WirelessPowerDiscovery
{
    protected function getHardwareName()
    {
        $rewrite_fortiap_hardware = [
            '.1.3.6.1.4.1.12356.120.10.23112' => 'FAP-231F',
            '.1.3.6.1.4.1.12356.120.10.23412' => 'FAP-234F',
            '.1.3.6.1.4.1.12356.120.10.23912' => 'FAP-23JF',
            '.1.3.6.1.4.1.12356.120.10.43112' => 'FAP-431F',
            '.1.3.6.1.4.1.12356.120.10.43212' => 'FAP-432F',
            '.1.3.6.1.4.1.12356.120.10.43312' => 'FAP-433F',
            '.1.3.6.1.4.1.12356.120.10.83112' => 'FAP-831F',
            '.1.3.6.1.4.1.12356.120.10.22111' => 'FAP-221E',
            '.1.3.6.1.4.1.12356.120.10.22211' => 'FAP-222E',
            '.1.3.6.1.4.1.12356.120.10.22311' => 'FAP-223E',
            '.1.3.6.1.4.1.12356.120.10.22411' => 'FAP-224E',
            '.1.3.6.1.4.1.12356.120.10.23111' => 'FAP-231E',
            '.1.3.6.1.4.1.12356.120.10.32111' => 'FAP-321E',
            '.1.3.6.1.4.1.12356.120.10.42111' => 'FAP-421E',
            '.1.3.6.1.4.1.12356.120.10.42311' => 'FAP-423E',
            '.1.3.6.1.4.1.12356.120.10.22131' => 'FAP-S221E',
            '.1.3.6.1.4.1.12356.120.10.22331' => 'FAP-S223E',
            '.1.3.6.1.4.1.12356.120.10.42131' => 'FAP-S421E',
            '.1.3.6.1.4.1.12356.120.10.42231' => 'FAP-S422E',
            '.1.3.6.1.4.1.12356.120.10.42331' => 'FAP-S423E',
            '.1.3.6.1.4.1.12356.120.10.24941' => 'FAP-C24JE',
            '.1.3.6.1.4.1.12356.120.10.22121' => 'FAP-U221EV',
            '.1.3.6.1.4.1.12356.120.10.22321' => 'FAP-U223EV',
            '.1.3.6.1.4.1.12356.120.10.24921' => 'FAP-U24JEV',
            '.1.3.6.1.4.1.12356.120.10.32121' => 'FAP-U321EV',
            '.1.3.6.1.4.1.12356.120.10.32321' => 'FAP-U323EV',
            '.1.3.6.1.4.1.12356.120.10.42121' => 'FAP-U421EV',
            '.1.3.6.1.4.1.12356.120.10.42221' => 'FAP-U422EV',
            '.1.3.6.1.4.1.12356.120.10.42321' => 'FAP-U423EV',
            '.1.3.6.1.4.1.12356.120.10.23122' => 'FAP-U231F',
            '.1.3.6.1.4.1.12356.120.10.23422' => 'FAP-U234F',
            '.1.3.6.1.4.1.12356.120.10.43122' => 'FAP-U431F',
            '.1.3.6.1.4.1.12356.120.10.43222' => 'FAP-U432F',
            '.1.3.6.1.4.1.12356.120.10.43322' => 'FAP-U433F',
        ];

        return $rewrite_fortiap_hardware[$this->getDevice()->sysObjectID] ?? null;
    }

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $device->hardware = $device->hardware ?: $this->getHardwareName();
    }

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
