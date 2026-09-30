<?php

/**
 * Openwrt.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\Number;
use SnmpQuery;

class Openwrt extends OS implements
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessPowerDiscovery,
    WirelessRateDiscovery,
    WirelessSnrDiscovery,
    WirelessUtilizationDiscovery
{
    public function discoverWirelessClients()
    {
        $sensors = [];
        foreach ($this->wirelessTable() as $ifIndex => $row) {
            if (isset($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceClients'])) {
                $sensors[] = new WirelessSensor(
                    WirelessSensorType::Clients,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.66510.1.10.3.1.4.' . $ifIndex,
                    'openwrt',
                    (string) $ifIndex,
                    $this->wirelessLabel($row, $ifIndex),
                    Number::extract($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceClients'])
                );
            }
        }

        $total = count($sensors) > 1 ? SnmpQuery::get('OPENWRT-WIRELESS-MIB::openwrtWirelessClientCount.0')->value() : '';
        if ($total !== '') {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Clients,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.66510.1.10.2.0',
                'openwrt',
                'total',
                'Total clients',
                Number::extract($total)
            );
        }

        return $sensors;
    }

    public function discoverWirelessFrequency()
    {
        $sensors = [];
        foreach ($this->wirelessTable() as $ifIndex => $row) {
            if (isset($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceFrequency'])) {
                $sensors[] = new WirelessSensor(
                    WirelessSensorType::Frequency,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.66510.1.10.3.1.5.' . $ifIndex,
                    'openwrt',
                    (string) $ifIndex,
                    $this->wirelessLabel($row, $ifIndex),
                    Number::extract($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceFrequency'])
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessNoiseFloor()
    {
        $sensors = [];
        foreach ($this->wirelessTable() as $ifIndex => $row) {
            if (isset($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceNoiseFloor'])) {
                $sensors[] = new WirelessSensor(
                    WirelessSensorType::NoiseFloor,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.66510.1.10.3.1.6.' . $ifIndex,
                    'openwrt',
                    (string) $ifIndex,
                    $this->wirelessLabel($row, $ifIndex),
                    Number::extract($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceNoiseFloor'])
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessRate()
    {
        $sensors = [];
        foreach ([
            ['OPENWRT-WIRELESS-MIB::openwrtWlIfaceTxRateMin', '.1.3.6.1.4.1.66510.1.10.3.1.7', 'openwrt-tx', 'min'],
            ['OPENWRT-WIRELESS-MIB::openwrtWlIfaceTxRateAvg', '.1.3.6.1.4.1.66510.1.10.3.1.8', 'openwrt-tx', 'avg'],
            ['OPENWRT-WIRELESS-MIB::openwrtWlIfaceTxRateMax', '.1.3.6.1.4.1.66510.1.10.3.1.9', 'openwrt-tx', 'max'],
            ['OPENWRT-WIRELESS-MIB::openwrtWlIfaceRxRateMin', '.1.3.6.1.4.1.66510.1.10.3.1.10', 'openwrt-rx', 'min'],
            ['OPENWRT-WIRELESS-MIB::openwrtWlIfaceRxRateAvg', '.1.3.6.1.4.1.66510.1.10.3.1.11', 'openwrt-rx', 'avg'],
            ['OPENWRT-WIRELESS-MIB::openwrtWlIfaceRxRateMax', '.1.3.6.1.4.1.66510.1.10.3.1.12', 'openwrt-rx', 'max'],
        ] as [$column, $oid, $subtype, $stat]) {
            foreach ($this->wirelessTable() as $ifIndex => $row) {
                if (isset($row[$column])) {
                    $sensors[] = new WirelessSensor(
                        WirelessSensorType::Rate,
                        $this->getDeviceId(),
                        $oid . '.' . $ifIndex,
                        $subtype,
                        "$subtype-$ifIndex-$stat",
                        $this->wirelessLabel($row, $ifIndex) . " $stat",
                        Number::extract($row[$column]) * 1000000, // the agent reports Mbit/s, LibreNMS stores bps
                        1000000
                    );
                }
            }
        }

        return $sensors;
    }

    public function discoverWirelessSnr()
    {
        $sensors = [];
        foreach ([
            ['OPENWRT-WIRELESS-MIB::openwrtWlIfaceSnrMin', '.1.3.6.1.4.1.66510.1.10.3.1.13', 'min'],
            ['OPENWRT-WIRELESS-MIB::openwrtWlIfaceSnrAvg', '.1.3.6.1.4.1.66510.1.10.3.1.14', 'avg'],
            ['OPENWRT-WIRELESS-MIB::openwrtWlIfaceSnrMax', '.1.3.6.1.4.1.66510.1.10.3.1.15', 'max'],
        ] as [$column, $oid, $stat]) {
            foreach ($this->wirelessTable() as $ifIndex => $row) {
                if (isset($row[$column])) {
                    $sensors[] = new WirelessSensor(
                        WirelessSensorType::Snr,
                        $this->getDeviceId(),
                        $oid . '.' . $ifIndex,
                        'openwrt',
                        "openwrt-$ifIndex-$stat",
                        $this->wirelessLabel($row, $ifIndex) . " $stat",
                        Number::extract($row[$column])
                    );
                }
            }
        }

        return $sensors;
    }

    public function discoverWirelessUtilization()
    {
        $sensors = [];
        foreach ($this->wirelessTable() as $ifIndex => $row) {
            if (isset($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceChannelUtil'])) {
                $sensors[] = new WirelessSensor(
                    WirelessSensorType::Utilization,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.66510.1.10.3.1.16.' . $ifIndex,
                    'openwrt',
                    (string) $ifIndex,
                    $this->wirelessLabel($row, $ifIndex),
                    Number::extract($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceChannelUtil'])
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessPower()
    {
        $sensors = [];
        foreach ($this->wirelessTable() as $ifIndex => $row) {
            if (isset($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceTxPower'])) {
                $sensors[] = new WirelessSensor(
                    WirelessSensorType::Power,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.66510.1.10.3.1.17.' . $ifIndex,
                    'openwrt',
                    (string) $ifIndex,
                    $this->wirelessLabel($row, $ifIndex),
                    Number::extract($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceTxPower'])
                );
            }
        }

        return $sensors;
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    private function wirelessTable(): array
    {
        return SnmpQuery::cache()->walk('OPENWRT-WIRELESS-MIB::openwrtWirelessInterfaceTable')->table(1);
    }

    private function wirelessLabel(array $row, int|string $ifIndex): string
    {
        return trim((string) ($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceLabel'] ?? '')) ?: (string) $ifIndex;
    }
}
