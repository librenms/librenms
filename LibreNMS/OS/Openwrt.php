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
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\Oid;

class Openwrt extends OS implements
    OSDiscovery,
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessRateDiscovery,
    WirelessSnrDiscovery
{
    /**
     * Retrieve basic information about the OS / device
     */
    public function discoverOS(Device $device): void
    {
        $distro = trim((string) snmp_get($this->getDeviceArray(), 'NET-SNMP-EXTEND-MIB::nsExtendOutput1Line."distro"', '-Osqnv'));
        $distroParts = preg_split('/\s+/', $distro, 2);
        $device->version = $distroParts[1] ?? $distro;
        $device->hardware = snmp_get($this->getDeviceArray(), 'NET-SNMP-EXTEND-MIB::nsExtendOutput1Line."hardware"', '-Osqnv');
    }

    /**
     * Retrieve (and explode to array) list of network interfaces, and desired display name in LibreNMS.
     * This information is returned from the wireless device (router / AP) - as SNMP extend, with the name "interfaces".
     *
     * @return array Interfaces
     */
    private function getInterfaces()
    {
        $rawInterfaces = (string) snmp_get($this->getDeviceArray(), 'NET-SNMP-EXTEND-MIB::nsExtendOutputFull."interfaces"', '-Osqnv');
        $interfaces = preg_split('/\r\n|\r|\n/', trim($rawInterfaces)) ?: [];
        $arrIfaces = [];
        foreach ($interfaces as $interface) {
            $interface = trim($interface);

            // Skip empty and comment lines
            if ($interface === '' || str_starts_with($interface, '#')) {
                continue;
            }

            $parsed = $this->parseInterfaceLine($interface);
            if ($parsed !== null) {
                [$k, $v] = $parsed;
                $arrIfaces[$k] = $v;
            }
        }

        return $arrIfaces;
    }

    /**
     * Parse a single interface mapping line.
     *
     * Supported formats:
     * - current: "wlan0,radio0 (SSID)"
     * - legacy:  "wlan0 wl-2.4G"
     * - fallback: "wlan0" (name maps to itself)
     *
     * @return array<string>|null [interface, description]
     */
    private function parseInterfaceLine(string $interface): ?array
    {
        // Preferred format used by current OpenWrt helper scripts.
        $parts = explode(',', $interface, 2);
        if (count($parts) === 2) {
            [$key, $value] = array_map(trim(...), $parts);

            if ($key !== '' && $value !== '') {
                return [$key, $value];
            }
        }

        // Legacy wlInterfaces.txt style with whitespace separator.
        $legacyParts = preg_split('/\s+/', $interface, 2) ?: [];
        if (count($legacyParts) === 2) {
            [$key, $value] = array_map(trim(...), $legacyParts);

            if ($key !== '' && $value !== '') {
                return [$key, $value];
            }
        }

        // Final fallback for single-token lines.
        if ($interface !== '') {
            return [$interface, $interface];
        }

        return null;
    }

    /**
     * Extract SSID from interface description
     *
     * @param  string  $interface  Interface description in format "iface (SSID)" or just "iface"
     * @return string SSID or interface name
     */
    private function extractSSID($interface)
    {
        // Match "interface (SSID)" pattern and extract SSID
        if (preg_match('/\(([^)]+)\)/', $interface, $matches)) {
            $ssid = trim($matches[1]);

            // Return SSID if not empty, otherwise return interface name
            return $ssid !== '' ? $ssid : preg_replace('/\s*\(.*?\)\s*/', '', $interface);
        }

        return $interface;
    }

    /**
     * Generic (common / shared) routine, to create new Wireless Sensors, of the sensor Type passed as the call argument.
     * type - string, matching to LibreNMS documentation => https://docs.librenms.org/Developing/os/Wireless-Sensors/
     * query - string, query to be used at client (appends to type string, e.g. -tx, -rx)
     * system - boolean, flag to indicate that a combined ("system level") sensor (and OID) is to be added
     * stats - boolean, flag denoting that statistics are to be retrieved (min, max, avg)
     * NOTE: system and stats are assumed to be mutually exclusive (at least for now!)
     *
     * @return array Sensors
     */
    private function getSensorData(WirelessSensorType $type, $query = '', $system = false, $stats = false)
    {
        // Initialize needed variables, and get interfaces (actual network name, and LibreNMS name)
        $sensors = [];
        $interfaces = $this->getInterfaces();
        $count = 1;

        // Build array for stats - if desired
        $statstr = [''];
        if ($stats) {
            $statstr = ['-min', '-max', '-avg'];
        }

        // Loop over interfaces, adding sensors
        foreach ($interfaces as $index => $interface) {
            $ssid = $this->extractSSID($interface);

            // Loop over stats, appending to sensors as needed (only a single, blank, addition if no stats)
            foreach ($statstr as $stat) {
                $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.1.' . Oid::encodeString("{$type->value}$query-$index$stat");

                // Format description: use SSID if available, otherwise interface name
                $description = $ssid . $query . $stat;

                $sensors[] = new WirelessSensor($type, $this->getDeviceId(), $oid, "openwrt$query", $count, $description);
                $count += 1;
            }
        }
        // If system level (i.e. overall) sensor desired, add that one as well
        if ($system && (count($interfaces) > 1)) {
            $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.1.' . Oid::encodeString("{$type->value}$query-wlan");
            $sensors[] = new WirelessSensor($type, $this->getDeviceId(), $oid, "openwrt$query", $count, 'wlan');
        }

        // And, return all the sensors that have been created above (i.e. the array of sensors)
        return $sensors;
    }

    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        return $this->getSensorData(WirelessSensorType::Clients, '', true, false);
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        return $this->getSensorData(WirelessSensorType::Frequency, '', false, false);
    }

    /**
     * Discover wireless noise floor.  This is in dBm. Type is noise-floor.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessNoiseFloor()
    {
        return $this->getSensorData(WirelessSensorType::NoiseFloor, '', false, false);
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $txrate = $this->getSensorData(WirelessSensorType::Rate, '-tx', false, true);
        $rxrate = $this->getSensorData(WirelessSensorType::Rate, '-rx', false, true);

        return array_merge($txrate, $rxrate);
    }

    /**
     * Discover wireless snr. This is in dB. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessSNR()
    {
        return $this->getSensorData(WirelessSensorType::Snr, '', false, true);
    }
}
