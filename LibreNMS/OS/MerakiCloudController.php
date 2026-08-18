<?php

/**
 * MerakiCloudController.php
 *
 * Cisco Meraki Cloud Controller (MERAKI-CLOUD-CONTROLLER-MIB)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessApCountPolling;
use LibreNMS\Interfaces\Polling\Sensors\WirelessClientsPolling;
use LibreNMS\OS;
use SnmpQuery;

class MerakiCloudController extends OS implements
    WirelessApCountDiscovery,
    WirelessApCountPolling,
    WirelessClientsDiscovery,
    WirelessClientsPolling
{
    private const DEV_STATUS_OID = '.1.3.6.1.4.1.29671.1.1.4.1.3.';
    private const DEV_CLIENT_COUNT_OID = '.1.3.6.1.4.1.29671.1.1.4.1.5.';

    /** @var array<string, array<string, mixed>>|null */
    private ?array $devices = null;

    /**
     * Discover wireless client counts. Type is clients.
     *
     * @return WirelessSensor[]
     */
    public function discoverWirelessClients(): array
    {
        $sensors = [];
        $oids = [];
        $total = 0;

        foreach ($this->devTable() as $index => $entry) {
            $clients = $entry['MERAKI-CLOUD-CONTROLLER-MIB::devClientCount'] ?? null;
            if ($clients === null) {
                continue;
            }

            $oid = self::DEV_CLIENT_COUNT_OID . $index;
            $oids[] = $oid;
            $total += (int) $clients;

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Clients,
                $this->getDeviceId(),
                $oid,
                'meraki-cloud-controller',
                $index,
                $entry['MERAKI-CLOUD-CONTROLLER-MIB::devName'] ?: $index,
                (int) $clients
            );
        }

        if (empty($oids)) {
            return [];
        }

        $sensors[] = new WirelessSensor(
            WirelessSensorType::Clients,
            $this->getDeviceId(),
            $oids,
            'meraki-cloud-controller',
            'total-clients',
            'Total Clients',
            $total
        );

        return $sensors;
    }

    /**
     * Discover the number of managed devices online. Type is ap-count.
     *
     * @return WirelessSensor[]
     */
    public function discoverWirelessApCount(): array
    {
        $oids = [];
        $online = 0;

        foreach ($this->devTable() as $index => $entry) {
            $status = $entry['MERAKI-CLOUD-CONTROLLER-MIB::devStatus'] ?? null;
            if ($status === null) {
                continue;
            }

            $oids[] = self::DEV_STATUS_OID . $index;
            $online += $this->isOnline($status) ? 1 : 0;
        }

        if (empty($oids)) {
            return [];
        }

        // devStatus is online(1)/offline(0), so the sum is the number of devices online
        return [
            new WirelessSensor(
                WirelessSensorType::ApCount,
                $this->getDeviceId(),
                $oids,
                'meraki-cloud-controller',
                'devices-online',
                'Devices Online',
                $online
            ),
        ];
    }

    /**
     * Poll wireless client counts.
     *
     * Polled from a single table walk rather than the individual sensor OIDs,
     * which avoids 35 separate gets against a slow endpoint.
     *
     * @param  array<int, array<string, mixed>>  $sensors
     * @return array<int, int>
     */
    public function pollWirelessClients(array $sensors)
    {
        $devices = $this->devTable();
        $data = [];
        $total = 0;

        foreach ($devices as $entry) {
            $total += (int) ($entry['MERAKI-CLOUD-CONTROLLER-MIB::devClientCount'] ?? 0);
        }

        foreach ($sensors as $sensor) {
            if ($sensor['sensor_index'] === 'total-clients') {
                $data[$sensor['sensor_id']] = $total;
            } elseif (isset($devices[$sensor['sensor_index']]['MERAKI-CLOUD-CONTROLLER-MIB::devClientCount'])) {
                $data[$sensor['sensor_id']] = (int) $devices[$sensor['sensor_index']]['MERAKI-CLOUD-CONTROLLER-MIB::devClientCount'];
            }
        }

        return $data;
    }

    /**
     * Poll the number of managed devices online.
     *
     * devStatus is an enum, so array_sum() based aggregation of the sensor OIDs
     * fails when SNMP returns the translated string instead of the number.
     *
     * @param  array<int, array<string, mixed>>  $sensors
     * @return array<int, int>
     */
    public function pollWirelessApCount(array $sensors)
    {
        $data = [];

        if (count($sensors) === 1) {
            $online = 0;

            foreach ($this->devTable() as $entry) {
                if ($this->isOnline($entry['MERAKI-CLOUD-CONTROLLER-MIB::devStatus'] ?? null)) {
                    $online++;
                }
            }

            $data[$sensors[0]['sensor_id']] = $online;
        }

        return $data;
    }

    /**
     * devStatus is offline(0)/online(1). Depending on whether the MIB is
     * available, SNMP returns either the number or the translated string.
     */
    private function isOnline(mixed $status): bool
    {
        return $status === 'online' || (is_numeric($status) && (int) $status === 1);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function devTable(): array
    {
        if ($this->devices === null) {
            // Walk only the needed columns. The Meraki cloud endpoint throttles
            // large walks, and a full devTable walk returns ragged/partial rows.
            $this->devices = SnmpQuery::cache()->numericIndex()->walk([
                'MERAKI-CLOUD-CONTROLLER-MIB::devName',
                'MERAKI-CLOUD-CONTROLLER-MIB::devStatus',
                'MERAKI-CLOUD-CONTROLLER-MIB::devClientCount',
            ])->valuesByIndex();
        }

        return $this->devices;
    }
}
