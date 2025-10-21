<?php

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Sensor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessChannelDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessClientsPolling;
use LibreNMS\OS;
use SnmpQuery;

class GrandstreamAp extends OS implements
    OSDiscovery,
    WirelessChannelDiscovery,
    WirelessClientsDiscovery,
    WirelessClientsPolling
{
    public function discoverOS(Device $device): void
    {
        $response = SnmpQuery::get([
            'GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnDeviceVersion.0',
            'GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnDeviceModel.0',
        ])->values();

        $device->version = $response['GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnDeviceVersion.0'] ?: null;
        $device->hardware = $response['GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnDeviceModel.0'] ?: null;
    }

    public function discoverWirelessClients(): array
    {
        $sensors = [];

        // Fetch all configured SSIDs
        $ssid_data = SnmpQuery::walk('GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnWlanESSID')->table(1);
        $unique_ssids = [];
        foreach ($ssid_data as $entry) {
            $essid = $entry['GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnWlanESSID'] ?? null;
            if ($essid !== null) {
                $unique_ssids[$essid] = true;
            }
        }
        $unique_ssids = array_keys($unique_ssids);

        // Fetch all client SSIDs
        $client_data = SnmpQuery::walk('GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnClientESSID')->table(0);

        // Assemble Sensors
        $total_clients = 0;
        foreach ($unique_ssids as $ssid_name) {
            if (str_starts_with($ssid_name, 'GWN-MESH-')) {
                continue;
            }

            $description = sprintf('SSID %s Clients', $ssid_name);
            $client_count = 0;
            $client_list = $client_data['GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnClientESSID'] ?? null;
            if (is_array($client_list) && ! empty($client_list)) {
                foreach ($client_data['GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnClientESSID'] as $essid) {
                    if ($essid === $ssid_name) {
                        $client_count++;
                    }
                }
            }
            $total_clients += $client_count;
            $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), [], 'grandstream-ap', $ssid_name, $description, $client_count);
        }

        $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), [], 'grandstream-ap', 'total-clients', 'Total Clients', $total_clients);

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
            $clients = SnmpQuery::walk('GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnClientESSID')->values();
            $ssid_counts = array_count_values($clients);
            $ssid_counts['total-clients'] = count($clients); // insert total-clients for nice logic below
            foreach ($sensors as $sensor) {
                $data[$sensor->sensor_id] = $ssid_counts[$sensor->sensor_index] ?? 0;
            }
        }

        return $data;
    }

    public function discoverWirelessChannel(): array
    {
        $sensors = [];

        $carrier = SnmpQuery::cache()->walk('GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnRadioName')->valuesByIndex();
        $data = SnmpQuery::walk('GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnRadioChannel')->valuesByIndex($carrier);

        foreach ($data as $index => $entry) {
            if (isset($entry['GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnRadioChannel'])) {
                $sensors[] = new WirelessSensor(
                    'channel',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.42397.1.1.3.1.1.4.' . $index,
                    'grandstream-ap',
                    $index,
                    'CHANNEL: ' . $entry['GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnRadioName'],
                    $entry['GRANDSTREAM-GWN-PRODUCTS-AP-MIB::gwnRadioChannel']
                );
            }
        }

        return $sensors;
    }
}
