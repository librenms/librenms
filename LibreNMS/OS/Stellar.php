<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessClientsPolling;
use LibreNMS\OS;

class Stellar extends OS implements
    WirelessClientsDiscovery,
    WirelessClientsPolling
{
    public function discoverWirelessClients()
    {
        $sensors = [];
        $device = $this->getDeviceArray();

        $ssid = [];
        $ssid_data = $this->getCacheTable('apWlanEssid', $device['hardware']);

        foreach ($ssid_data as $ssid_entry) {
            if ($ssid_entry['apWlanEssid'] == 'athmon2') {
                continue;
            } elseif (array_key_exists($ssid_entry['apWlanEssid'], $ssid)) {
                continue;
            } else {
                $ssid[$ssid_entry['apWlanEssid']] = 0;
            }
        }

        $client_ws_data = $this->getCacheTable('apClientWlanService', $device['hardware']);

        if (empty($client_ws_data)) {
            $total_clients = 0;
        } else {
            $total_clients = sizeof($client_ws_data);
        }

        $combined_oid = sprintf('%s::%s', $device['hardware'], 'apClientWlanService');
        $oid = snmp_translate($combined_oid, 'ALL', 'nokia/stellar', '-On');

        foreach ($client_ws_data as $client_entry) {
            $ssid[$client_entry['apClientWlanService']] += 1;
        }

        foreach ($ssid as $key => $value) {
            $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), $oid, 'stellar', $key, 'SSID ' . $key . ' Clients', $value);
        }

        $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), $oid, 'stellar', 'total-clients', 'Total Clients', $total_clients);

        return $sensors;
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

            $client_ws_data = $this->getCacheTable('apClientWlanService', $device['hardware']);

            if (empty($client_ws_data)) {
                $total_clients = 0;
            } else {
                $total_clients = sizeof($client_ws_data);
            }

            foreach ($sensors as $sensor) {
                foreach ($client_ws_data as $cliententry) {
                    if ($cliententry['apClientWlanService'] == $sensor['sensor_index']) {
                        $data[$sensor['sensor_id']] += 1;
                    }
                }
            }

            $data[$sensors[0]['sensor_id']] = $total_clients;
        }

        return $data;
    }
}
