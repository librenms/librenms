<?php

namespace LibreNMS\OS;

use App\Models\Sensor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessClientsPolling;
use LibreNMS\OS;

class Stellar extends OS implements
    WirelessClientsDiscovery,
    WirelessClientsPolling
{
    public function discoverWirelessClients(): array
    {
        $sensors = [];
        $mib = $this->getDevice()->hardware;

        $ssid_data = \SnmpQuery::mibs([$mib])->walk('apWlanEssid')->values();
        $clients = \SnmpQuery::mibs([$mib])->walk('apClientWlanService')->values();

        $total_clients = count($clients);
        $ssid_counts = array_count_values($clients);

        foreach ($ssid_data as $ssid_name) {
            if ($ssid_name === 'athmon2') {
                continue;
            }

            $ssid_count = $ssid_counts[$ssid_name] ?? 0;
            $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), [], 'stellar', $ssid_name, 'SSID ' . $ssid_name . ' Clients', $ssid_count);
        }

        $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), [], 'stellar', 'total-clients', 'Total Clients', $total_clients);

        return $sensors;
    }

    /**
     * Poll wireless clients
     * The returned array should be sensor_id => value pairs
     *
     * @param  Sensor[]  $sensors  Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessClients(array $sensors): array
    {
        $data = [];
        if (! empty($sensors)) {
            $mib = $this->getDevice()->hardware;
            $clients = \SnmpQuery::mibs([$mib])->walk('apClientWlanService')->values();
            $ssid_counts = array_count_values($clients);
            $ssid_counts['total-clients'] = count($clients); // insert total-clients for nice logic below

            foreach ($sensors as $sensor) {
                $data[$sensor->sensor_id] = $ssid_counts[$sensor->sensor_index] ?? 0;
            }
        }

        return $data;
    }
}
