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

use App\Models\Device;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\OS;

class Openwrt extends OS implements
    OSDiscovery,
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessPowerDiscovery,
    WirelessRateDiscovery,
    WirelessSnrDiscovery,
    WirelessUtilizationDiscovery
{
    // OPENWRT-WIRELESS-MIB openwrtWirelessInterfaceEntry columns, addressed as
    // <WL_ENTRY>.<column>.<ifIndex>. Wireless data is served by the agent's
    // pass_persist handler under { openwrtObjects 10 } (.60652.102.1.10).
    private const WL_ENTRY = '.1.3.6.1.4.1.60652.102.1.10.3.1';

    // openwrtWirelessClientCount scalar: device-wide de-duplicated client count.
    private const WL_CLIENT_COUNT = '.1.3.6.1.4.1.60652.102.1.10.2.0';

    // Rates are exported in Mbit/s (so Wi-Fi 6E/7 multi-Gbit/s rates do not
    // overflow Unsigned32); LibreNMS Rate sensors are stored in bps.
    private const RATE_MULTIPLIER = 1000000;

    /** @var array<int, string>|null cache of ifIndex => label */
    private ?array $wlInterfaces = null;

    /**
     * Retrieve basic information about the OS / device
     */
    public function discoverOS(Device $device): void
    {
        $distro = trim((string) \SnmpQuery::get('NET-SNMP-EXTEND-MIB::nsExtendOutput1Line."distro"')->value());
        $distroParts = preg_split('/\s+/', $distro, 2);
        $device->version = $distroParts[1] ?? $distro;
        $device->hardware = \SnmpQuery::get('NET-SNMP-EXTEND-MIB::nsExtendOutput1Line."hardware"')->value();
    }

    /**
     * Wireless interfaces keyed by IF-MIB ifIndex, mapped to their display
     * label (SSID). Walked once from openwrtWlIfaceLabel and cached; the table
     * is ifIndex-indexed so every metric column shares these indexes.
     *
     * @return array<int, string>
     */
    private function wirelessInterfaces(): array
    {
        if ($this->wlInterfaces !== null) {
            return $this->wlInterfaces;
        }

        $labelOid = self::WL_ENTRY . '.3'; // openwrtWlIfaceLabel
        $this->wlInterfaces = [];

        foreach (\SnmpQuery::walk($labelOid)->values() as $oid => $label) {
            $ifIndex = (int) substr((string) $oid, strrpos((string) $oid, '.') + 1);
            if ($ifIndex > 0) {
                $this->wlInterfaces[$ifIndex] = trim((string) $label);
            }
        }

        return $this->wlInterfaces;
    }

    /**
     * One single-value sensor per wireless interface, reading a single table
     * column.
     *
     * @return array<WirelessSensor>
     */
    private function perInterfaceSensors(WirelessSensorType $type, int $column, int $multiplier = 1): array
    {
        $sensors = [];
        foreach ($this->wirelessInterfaces() as $ifIndex => $label) {
            $sensors[] = new WirelessSensor(
                $type,
                $this->getDeviceId(),
                self::WL_ENTRY . '.' . $column . '.' . $ifIndex,
                'openwrt',
                (string) $ifIndex,
                $label !== '' ? $label : (string) $ifIndex,
                null,
                $multiplier
            );
        }

        return $sensors;
    }

    /**
     * min / avg / max sensors per wireless interface, read from three
     * sequential table columns. The MIB SEQUENCE order is Min, Avg, Max, so
     * the columns map $minColumn => min, +1 => avg, +2 => max.
     *
     * @return array<WirelessSensor>
     */
    private function statsSensors(WirelessSensorType $type, string $subtype, int $minColumn, int $multiplier = 1): array
    {
        $stats = ['min' => $minColumn, 'avg' => $minColumn + 1, 'max' => $minColumn + 2];

        $sensors = [];
        foreach ($this->wirelessInterfaces() as $ifIndex => $label) {
            $name = $label !== '' ? $label : (string) $ifIndex;
            foreach ($stats as $stat => $column) {
                $sensors[] = new WirelessSensor(
                    $type,
                    $this->getDeviceId(),
                    self::WL_ENTRY . '.' . $column . '.' . $ifIndex,
                    $subtype,
                    $subtype . '-' . $ifIndex . '-' . $stat,
                    $name . ' ' . $stat,
                    null,
                    $multiplier
                );
            }
        }

        return $sensors;
    }

    /**
     * Discover wireless client counts (per interface plus a device-wide total).
     *
     * @return array
     */
    public function discoverWirelessClients()
    {
        $sensors = $this->perInterfaceSensors(WirelessSensorType::Clients, 4);

        // Device-wide de-duplicated client count only adds information when
        // more than one interface serves clients.
        if (count($sensors) > 1) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Clients,
                $this->getDeviceId(),
                self::WL_CLIENT_COUNT,
                'openwrt',
                'total',
                'Total clients'
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless frequency. This is in MHz.
     *
     * @return array
     */
    public function discoverWirelessFrequency()
    {
        return $this->perInterfaceSensors(WirelessSensorType::Frequency, 5);
    }

    /**
     * Discover wireless noise floor. This is in dBm.
     *
     * @return array
     */
    public function discoverWirelessNoiseFloor()
    {
        return $this->perInterfaceSensors(WirelessSensorType::NoiseFloor, 6);
    }

    /**
     * Discover wireless rate (tx and rx, min/avg/max). Stored in bps.
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $tx = $this->statsSensors(WirelessSensorType::Rate, 'openwrt-tx', 7, self::RATE_MULTIPLIER);
        $rx = $this->statsSensors(WirelessSensorType::Rate, 'openwrt-rx', 10, self::RATE_MULTIPLIER);

        return array_merge($tx, $rx);
    }

    /**
     * Discover wireless SNR (min/avg/max). This is in dB.
     *
     * @return array
     */
    public function discoverWirelessSNR()
    {
        return $this->statsSensors(WirelessSensorType::Snr, 'openwrt', 13);
    }

    /**
     * Discover per-interface channel airtime utilisation. This is a percentage.
     *
     * @return array
     */
    public function discoverWirelessUtilization()
    {
        return $this->perInterfaceSensors(WirelessSensorType::Utilization, 16);
    }

    /**
     * Discover per-interface transmit power. This is in dBm.
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        return $this->perInterfaceSensors(WirelessSensorType::Power, 17);
    }
}
