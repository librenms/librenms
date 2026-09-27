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
    private const STATS = ['min' => 'Min', 'avg' => 'Avg', 'max' => 'Max'];

    /** @var array<string, string>|null ifIndex => label */
    private ?array $labels = null;

    public function discoverWirelessClients()
    {
        $sensors = $this->ifaceSensors(WirelessSensorType::Clients, 'openwrtWlIfaceClients');

        if (count($sensors) > 1) {
            $total = SnmpQuery::numeric()->get('OPENWRT-WIRELESS-MIB::openwrtWirelessClientCount.0')->values();
            if ($total) {
                $sensors[] = new WirelessSensor(
                    WirelessSensorType::Clients,
                    $this->getDeviceId(),
                    array_key_first($total),
                    'openwrt',
                    'total',
                    'Total clients'
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessFrequency()
    {
        return $this->ifaceSensors(WirelessSensorType::Frequency, 'openwrtWlIfaceFrequency');
    }

    public function discoverWirelessNoiseFloor()
    {
        return $this->ifaceSensors(WirelessSensorType::NoiseFloor, 'openwrtWlIfaceNoiseFloor');
    }

    public function discoverWirelessRate()
    {
        $sensors = [];
        foreach (['tx' => 'Tx', 'rx' => 'Rx'] as $dir => $column) {
            foreach (self::STATS as $stat => $suffix) {
                // the agent reports Mbit/s, LibreNMS stores bps
                array_push($sensors, ...$this->ifaceSensors(WirelessSensorType::Rate, "openwrtWlIface{$column}Rate$suffix", "openwrt-$dir", $stat, 1000000));
            }
        }

        return $sensors;
    }

    public function discoverWirelessSnr()
    {
        $sensors = [];
        foreach (self::STATS as $stat => $suffix) {
            array_push($sensors, ...$this->ifaceSensors(WirelessSensorType::Snr, "openwrtWlIfaceSnr$suffix", 'openwrt', $stat));
        }

        return $sensors;
    }

    public function discoverWirelessUtilization()
    {
        return $this->ifaceSensors(WirelessSensorType::Utilization, 'openwrtWlIfaceChannelUtil');
    }

    public function discoverWirelessPower()
    {
        return $this->ifaceSensors(WirelessSensorType::Power, 'openwrtWlIfaceTxPower');
    }

    /**
     * One sensor per wireless interface that reports the given column.
     *
     * @return WirelessSensor[]
     */
    private function ifaceSensors(WirelessSensorType $type, string $column, string $subtype = 'openwrt', ?string $stat = null, int $multiplier = 1): array
    {
        $this->labels ??= SnmpQuery::walk('OPENWRT-WIRELESS-MIB::openwrtWlIfaceLabel')->pluck();

        $sensors = [];
        foreach (SnmpQuery::numeric()->walk("OPENWRT-WIRELESS-MIB::$column")->groupByIndex() as $ifIndex => $values) {
            $name = trim($this->labels[$ifIndex] ?? '') ?: (string) $ifIndex;

            $sensors[] = new WirelessSensor(
                $type,
                $this->getDeviceId(),
                array_key_first($values),
                $subtype,
                $stat === null ? (string) $ifIndex : "$subtype-$ifIndex-$stat",
                $stat === null ? $name : "$name $stat",
                null,
                $multiplier
            );
        }

        return $sensors;
    }
}
