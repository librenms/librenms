<?php

/*
 * Fortigate.php
 *
 * -Description-
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Location;
use Illuminate\Support\Facades\Log;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Interfaces\Polling\Sensors\WirelessApCountPolling;
use LibreNMS\Interfaces\Polling\Sensors\WirelessClientsPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Bgp;
use LibreNMS\Util\IP;
use SnmpQuery;

class Fortigate extends OS implements
    OSPolling,
    WirelessApCountDiscovery,
    WirelessApCountPolling,
    WirelessClientsDiscovery,
    WirelessClientsPolling
{
    // fgWcInfoWtpSessions.0 and fgWcInfoStationCount.0. VDOM scoped; kept as sensor_oids so existing
    // sensor rows do not change.
    private const string WTP_SESSIONS_OID = '.1.3.6.1.4.1.12356.101.14.2.5.0';
    private const string STATION_COUNT_OID = '.1.3.6.1.4.1.12356.101.14.2.7.0';

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
    }

    /**
     * With append-index enabled (config system snmp sysinfo, FortiOS 7.2.8+ and 7.4.4+),
     * bgpLocalAs is returned at the VDOM snmp-index instead of .0
     */
    public function discoverBgpLocalAs(): ?int
    {
        return $this->bgpLocalAsFallback(parent::discoverBgpLocalAs(), 'BGP4-MIB::bgpLocalAs');
    }

    /**
     * With append-index enabled, FortiOS appends the VDOM snmp-index to bgpPeerTable indexes
     * instead of using SNMP contexts. Multi VDOM units need it enabled to return peers from every VDOM.
     * The extra index is dropped, so the same peer address in two VDOMs is seen as one session.
     *
     * @param  string[]  $parts
     */
    protected function bgp4MibPeerAddress(array $parts): ?IP
    {
        return Bgp::shiftIp($parts, count($parts) == 5 ? 1 : 0);
    }

    public function pollOS(DataStorageInterface $datastore): void
    {
        $sessions = snmp_get($this->getDeviceArray(), 'FORTINET-FORTIGATE-MIB::fgSysSesCount.0', '-Ovq');
        if (is_numeric($sessions)) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0, 3000000);

            Log::info("Sessions: $sessions");
            $fields = [
                'sessions' => $sessions,
            ];

            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($this->getDeviceArray(), 'fortigate_sessions', $tags, $fields);
            $this->enableGraph('fortigate_sessions');
        }

        $cpu_usage = snmp_get($this->getDeviceArray(), 'FORTINET-FORTIGATE-MIB::fgSysCpuUsage.0', '-Ovq');
        if (is_numeric($cpu_usage)) {
            $rrd_def = RrdDefinition::make()->addDataset('LOAD', 'GAUGE', -1, 100);

            Log::info("CPU: $cpu_usage%");
            $fields = [
                'LOAD' => $cpu_usage,
            ];

            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($this->getDeviceArray(), 'fortigate_cpu', $tags, $fields);
            $this->enableGraph('fortigate_cpu');
        }
    }

    /**
     * @return array<int, WirelessSensor>
     */
    public function discoverWirelessClients()
    {
        return [
            new WirelessSensor(
                WirelessSensorType::Clients,
                $this->getDeviceId(),
                self::STATION_COUNT_OID,
                'fortigate',
                1,
                'Clients: Total',
                $this->countWirelessClients()
            ),
        ];
    }

    /**
     * @param  array<int, \App\Models\WirelessSensor>  $sensors
     * @return array<int, int>
     */
    public function pollWirelessClients(array $sensors)
    {
        return $this->fillSensors($sensors, $this->countWirelessClients());
    }

    /**
     * @return array<int, WirelessSensor>
     */
    public function discoverWirelessApCount()
    {
        return [
            new WirelessSensor(
                WirelessSensorType::ApCount,
                $this->getDeviceId(),
                self::WTP_SESSIONS_OID,
                'fortigate',
                1,
                'Connected APs',
                $this->countConnectedAps()
            ),
        ];
    }

    /**
     * @param  array<int, \App\Models\WirelessSensor>  $sensors
     * @return array<int, int>
     */
    public function pollWirelessApCount(array $sensors)
    {
        return $this->fillSensors($sensors, $this->countConnectedAps());
    }

    /**
     * Count the FortiAPs that hold a CAPWAP session, across every VDOM.
     *
     * fgWcInfoWtpSessions only counts the APs of the VDOM that answers the SNMP query, so it
     * reports 0 when the APs are managed in another VDOM. fgWcWtpSessionTable is indexed by
     * fgVdEntIndex and also lists the APs of other VDOMs, so count its rows that are not offLine.
     * Some devices count an AP in the scalar that the table does not list, so report the higher
     * of the two and never less than the scalar.
     *
     * @return int|null null when the device reports no wireless controller data
     */
    private function countConnectedAps(): ?int
    {
        $scalar = $this->wirelessScalar('FORTINET-FORTIGATE-MIB::fgWcInfoWtpSessions.0');
        $sessions = SnmpQuery::cache()->walk('FORTINET-FORTIGATE-MIB::fgWcWtpSessionWtpStationCount')->values();

        if (empty($sessions)) {
            return $scalar;
        }

        $states = SnmpQuery::cache()->enumStrings()
            ->walk('FORTINET-FORTIGATE-MIB::fgWcWtpSessionConnectionState')->values();
        $connected = count($sessions) - count(array_filter($states, fn ($state) => $state === 'offLine'));

        return max($connected, (int) $scalar);
    }

    /**
     * Count the wireless clients of every FortiAP, across every VDOM.
     *
     * fgWcInfoStationCount is VDOM scoped in the same way as fgWcInfoWtpSessions.
     *
     * @return int|null null when the device reports no wireless controller data
     */
    private function countWirelessClients(): ?int
    {
        $scalar = $this->wirelessScalar('FORTINET-FORTIGATE-MIB::fgWcInfoStationCount.0');
        $stations = SnmpQuery::cache()->walk('FORTINET-FORTIGATE-MIB::fgWcWtpSessionWtpStationCount')->values();

        if (empty($stations)) {
            return $scalar;
        }

        return max((int) array_sum($stations), (int) $scalar);
    }

    private function wirelessScalar(string $oid): ?int
    {
        $value = SnmpQuery::cache()->get($oid)->value();

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  array<int, \App\Models\WirelessSensor>  $sensors
     * @return array<int, int> sensor_id => value
     */
    private function fillSensors(array $sensors, ?int $value): array
    {
        if ($value === null) {
            return [];
        }

        $data = [];
        foreach ($sensors as $sensor) {
            $data[$sensor->sensor_id] = $value;
        }

        return $data;
    }

    public function fetchLocation(): Location
    {
        $location = parent::fetchLocation();

        $lat = SnmpQuery::get('FORTINET-FORTIGATE-MIB::fgLatitude.1')->value();
        $lng = SnmpQuery::get('FORTINET-FORTIGATE-MIB::fgLongitude.1')->value();

        if (preg_match('/^([\d.]+)\s+([NS])$/', (string) $lat, $m)) {
            $location->lat = (float) $m[1] * ($m[2] === 'S' ? -1 : 1);
        }

        if (preg_match('/^([\d.]+)\s+([EW])$/', (string) $lng, $m)) {
            $location->lng = (float) $m[1] * ($m[2] === 'W' ? -1 : 1);
        }

        return $location;
    }
}
