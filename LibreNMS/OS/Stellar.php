<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessClientsPolling;
use LibreNMS\OS;
use SnmpQuery;

class Stellar extends OS implements
    WirelessClientsDiscovery,
    WirelessClientsPolling
{
    public function discoverWirelessClients()
    {
        $sensors = [];
        $hardware = $this->getDevice()->hardware;

        $ssid = [];
        $ssid_data = SnmpQuery::hideMib()->mibs([$hardware])->walk('apWlanEssid')->table(1);

        foreach ($ssid_data as $ssid_entry) {
            if ($ssid_entry['apWlanEssid'] == 'athmon2') {
                continue;
            } elseif (array_key_exists($ssid_entry['apWlanEssid'], $ssid)) {
                continue;
            } else {
                $ssid[$ssid_entry['apWlanEssid']] = 0;
            }
        }

        $client_ws_data = SnmpQuery::hideMib()->mibs([$hardware])->walk('apClientWlanService')->table(1);

        if (empty($client_ws_data)) {
            $total_clients = 0;
        } else {
            $total_clients = count($client_ws_data);
        }

        $combined_oid = sprintf('%s::%s', $hardware, 'apClientWlanService');
        $oid = snmp_translate($combined_oid, 'ALL', 'nokia/stellar', '-On');

        if (empty($oid)) {
            return $sensors;
        }

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
     * @param  array  $sensors  Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessClients(array $sensors)
    {
        $data = [];
        if (! empty($sensors)) {
            $hardware = $this->getDevice()->hardware;

            $client_ws_data = SnmpQuery::hideMib()->mibs([$hardware])->walk('apClientWlanService')->table(1);

            if (empty($client_ws_data)) {
                $total_clients = 0;
            } else {
                $total_clients = count($client_ws_data);
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
