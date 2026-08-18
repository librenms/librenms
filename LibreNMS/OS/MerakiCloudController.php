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
use LibreNMS\OS;
use SnmpQuery;

class MerakiCloudController extends OS implements
    WirelessApCountDiscovery,
    WirelessClientsDiscovery
{
    private const DEV_STATUS_OID = '.1.3.6.1.4.1.29671.1.1.4.1.3.';
    private const DEV_CLIENT_COUNT_OID = '.1.3.6.1.4.1.29671.1.1.4.1.5.';

    private ?array $devices = null;

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
            $online += (int) $status;
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
