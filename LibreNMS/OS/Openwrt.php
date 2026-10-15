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
                    $this->wirelessKey($row, $ifIndex),
                    $this->wirelessLabel($row, $ifIndex),
                    $row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceClients']
                );
            }
        }

        $total = $this->wirelessData()[0]['OPENWRT-WIRELESS-MIB::openwrtWirelessClientCount'] ?? '';
        if (count($sensors) > 1 && $total !== '') {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Clients,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.66510.1.10.2.0',
                'openwrt',
                'total',
                'Total clients',
                (int) $total
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
                    $this->wirelessKey($row, $ifIndex),
                    $this->wirelessLabel($row, $ifIndex),
                    $row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceFrequency']
                );
            }
        }

        return array_merge($sensors, $this->linkSensors(WirelessSensorType::Frequency, 'OPENWRT-WIRELESS-MIB::openwrtWlLinkFrequency', '.1.3.6.1.4.1.66510.1.10.4.1.4'));
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
                    $this->wirelessKey($row, $ifIndex),
                    $this->wirelessLabel($row, $ifIndex),
                    $row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceNoiseFloor']
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
                        $subtype . '-' . $this->wirelessKey($row, $ifIndex) . "-$stat",
                        $this->wirelessLabel($row, $ifIndex) . " $stat",
                        $row[$column] * 1000000, // the agent reports Mbit/s, LibreNMS stores bps
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
                        'openwrt-' . $this->wirelessKey($row, $ifIndex) . "-$stat",
                        $this->wirelessLabel($row, $ifIndex) . " $stat",
                        $row[$column]
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
                    $this->wirelessKey($row, $ifIndex),
                    $this->wirelessLabel($row, $ifIndex),
                    $row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceChannelUtil']
                );
            }
        }

        return array_merge($sensors, $this->linkSensors(WirelessSensorType::Utilization, 'OPENWRT-WIRELESS-MIB::openwrtWlLinkChannelUtil', '.1.3.6.1.4.1.66510.1.10.4.1.6'));
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
                    $this->wirelessKey($row, $ifIndex),
                    $this->wirelessLabel($row, $ifIndex),
                    $row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceTxPower']
                );
            }
        }

        return array_merge($sensors, $this->linkSensors(WirelessSensorType::Power, 'OPENWRT-WIRELESS-MIB::openwrtWlLinkTxPower', '.1.3.6.1.4.1.66510.1.10.4.1.5'));
    }

    /**
     * One cached request returns the scalars and both tables, so discovering the sensor types does not
     * add queries. Row 0 holds the scalars, every other row is an interface keyed by ifIndex.
     *
     * @return array<int|string, array<string, mixed>>
     */
    private function wirelessData(): array
    {
        return SnmpQuery::cache()->walk([
            'OPENWRT-WIRELESS-MIB::openwrtWirelessClientCount',
            'OPENWRT-WIRELESS-MIB::openwrtWirelessInterfaceTable',
            'OPENWRT-WIRELESS-MIB::openwrtWirelessLinkTable',
        ])->table(1);
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    private function wirelessTable(): array
    {
        return array_filter($this->wirelessData(), fn ($ifIndex) => $ifIndex !== 0, ARRAY_FILTER_USE_KEY);
    }

    /**
     * A multi-link (802.11be MLO) interface has no single frequency, power or utilisation, those are
     * served per link in openwrtWirelessLinkTable (indexed by ifIndex and link id).
     *
     * @return WirelessSensor[]
     */
    private function linkSensors(WirelessSensorType $type, string $column, string $oidBase): array
    {
        $sensors = [];
        foreach ($this->wirelessTable() as $ifIndex => $row) {
            if (! is_array($row[$column] ?? null)) {
                continue;
            }

            foreach ($row[$column] as $linkId => $value) {
                $sensors[] = new WirelessSensor(
                    $type,
                    $this->getDeviceId(),
                    "$oidBase.$ifIndex.$linkId",
                    'openwrt',
                    $this->wirelessKey($row, $ifIndex) . "-link$linkId",
                    $this->wirelessLabel($row, $ifIndex) . " link $linkId",
                    $value
                );
            }
        }

        return $sensors;
    }

    /**
     * The kernel ifIndex of a wireless interface changes whenever the VAP is recreated (wifi reload,
     * hostapd restart, reboot), the interface name does not. Use the name as the stored sensor index so
     * history survives; the OID still carries the current ifIndex and is refreshed on discovery.
     */
    private function wirelessKey(array $row, int|string $ifIndex): string
    {
        return trim((string) ($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceName'] ?? '')) ?: (string) $ifIndex;
    }

    /**
     * The label is the SSID and is not unique (one SSID on several bands), so add the interface name.
     */
    private function wirelessLabel(array $row, int|string $ifIndex): string
    {
        $label = trim((string) ($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceLabel'] ?? ''));
        $name = trim((string) ($row['OPENWRT-WIRELESS-MIB::openwrtWlIfaceName'] ?? ''));

        if ($label === '') {
            return $name ?: (string) $ifIndex;
        }

        return $name === '' || $name === $label ? $label : "$label ($name)";
    }
}
