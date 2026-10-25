<?php

/**
 * Aos6.php
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
 * Base Alcatel-Lucent OS (AOS6)
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2025 Peca Nesovanovic
 * @copyright  2025 Tony Murray
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 * @author     Tony Murray <murraytony@gmail.com>
 * @author     Paul Iercosan <mail@paulierco.ro>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\PortsNac;
use App\Models\PortVlan;
use App\Models\Transceiver;
use App\Models\Vlan;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Discovery\VlanDiscovery;
use LibreNMS\Interfaces\Discovery\VlanPortDiscovery;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\OS;
use LibreNMS\Util\Mac;
use SnmpQuery;

class Aos6 extends OS implements VlanDiscovery, VlanPortDiscovery, TransceiverDiscovery, NacPolling
{
    private const PORTS_NAC_STR_MAX = 50;

    public function discoverVlans(): Collection
    {
        if (($QBridgeMibVlans = parent::discoverVlans())->isNotEmpty()) {
            return $QBridgeMibVlans;
        }

        return SnmpQuery::walk('ALCATEL-IND1-VLAN-MGR-MIB::vlanDescription')
            ->mapTable(fn ($vlans, $vlan_id) => new Vlan([
                'vlan_vlan' => $vlan_id,
                'vlan_name' => $vlans['ALCATEL-IND1-VLAN-MGR-MIB::vlanDescription'] ?? null,
                'vlan_domain' => 1,
                'vlan_type' => null,
            ]));
    }

    public function discoverVlanPorts(Collection $vlans): Collection
    {
        if (($QBridgeMibPorts = parent::discoverVlanPorts($vlans))->isNotEmpty()) {
            return $QBridgeMibPorts;
        }

        return SnmpQuery::walk('ALCATEL-IND1-VLAN-MGR-MIB::vpaType')
            ->mapTable(fn ($data, $vpaVlanNumber, $vpaIfIndex = null) => new PortVlan([
                'vlan' => $vpaVlanNumber,
                'baseport' => $this->bridgePortFromIfIndex($vpaIfIndex),
                'untagged' => ($data['ALCATEL-IND1-VLAN-MGR-MIB::vpaType'] == 1 ? 1 : 0),
                'port_id' => PortCache::getIdFromIfIndex($vpaIfIndex, $this->getDeviceId()) ?? 0,
            ]));
    }

    public function pollNac(): Collection
    {
        $rows = collect(
            SnmpQuery::mibDir('nokia/aos6')
                ->walk('ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusTable')
                ->valuesByIndex()
        );

        if ($rows->isEmpty()) {
            return collect();
        }

        /*
         * AOS6 does not expose the RADIUS server used per session.
         * Use the configured 802.1X/MAC authentication server lists instead.
         */
        $authByDot1x = $this->getNacRadiusServers(
            'ALCATEL-IND1-AAA-MIB::aaaAuth8021xTable',
            [
                'ALCATEL-IND1-AAA-MIB::aaatxName1',
                'ALCATEL-IND1-AAA-MIB::aaatxName2',
                'ALCATEL-IND1-AAA-MIB::aaatxName3',
                'ALCATEL-IND1-AAA-MIB::aaatxName4',
            ]
        );

        $authByMac = $this->getNacRadiusServers(
            'ALCATEL-IND1-AAA-MIB::aaaAuthMACTable',
            [
                'ALCATEL-IND1-AAA-MIB::aaaMacSrvrName1',
                'ALCATEL-IND1-AAA-MIB::aaaMacSrvrName2',
                'ALCATEL-IND1-AAA-MIB::aaaMacSrvrName3',
                'ALCATEL-IND1-AAA-MIB::aaaMacSrvrName4',
            ]
        );

        $uptimeTicks = (int) SnmpQuery::get('SNMPv2-MIB::sysUpTime.0')->value();
        $mib = 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatus';
        $nac = collect();
        $authenticatedMacs = []; // port_id => [mac => true]

        foreach ($rows as $row) {
            $slot = (int) ($row[$mib . 'SlotNumber'] ?? 0);
            $port = (int) ($row[$mib . 'PortNumber'] ?? 0);

            if ($slot <= 0 || $port <= 0) {
                continue;
            }

            // AOS6 physical ifIndex is slot * 1000 + port.
            $ifIndex = ($slot * 1000) + $port;
            $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId());

            if (! $portId) {
                continue;
            }

            $mac = Mac::parse((string) ($row[$mib . 'MACAddress'] ?? ''));

            if (! $mac->isValid()) {
                continue;
            }

            $vlan = (int) ($row[$mib . 'Vlan'] ?? 0);

            $profile = trim((string) ($row[$mib . 'ProfileUsed'] ?? ''));
            $domain = ($profile !== '' && $profile !== '--') ? $profile : 'UNP';

            $username = trim((string) ($row[$mib . 'UserName'] ?? ''));

            if ($username === '' || $username === '--') {
                $username = $mac->readable();
            }

            $method = match ((int) ($row[$mib . 'AuthType'] ?? 0)) {
                1 => 'dot1x',
                2 => 'mab',
                3 => 'captivePortal',
                default => 'unknown',
            };

            $authBy = match ($method) {
                'dot1x' => $authByDot1x,
                'mab' => $authByMac,
                default => 'RADIUS',
            };

            $authResult = (int) ($row[$mib . 'AuthResult'] ?? 0);

            $authcStatus = match ($authResult) {
                1 => 'authcInProgress',
                2 => 'authcSuccess',
                3 => 'authcFail',
                default => 'authcUnknown',
            };

            $authzStatus = match ($authResult) {
                1 => 'authzInProgress',
                2 => 'authzSuccess',
                3 => 'authzFail',
                default => 'authzUnknown',
            };

            if ($authResult === 2) {
                // The table is indexed by query type first, so a client can be listed more than once: count each MAC once.
                $authenticatedMacs[$portId][$mac->hex()] = true;
            }

            $nac->push(new PortsNac([
                'port_id' => $portId,
                'auth_id' => sprintf('%d-%s-%d', $ifIndex, $mac->hex(), $vlan),
                'domain' => $this->limitString($domain),
                'username' => $this->limitString($username),
                'mac_address' => $mac->hex(),
                'ip_address' => $this->normalizeIpAddress($row[$mib . 'IPAddress'] ?? null),
                'host_mode' => 'singleHost',
                'authz_status' => $authzStatus,
                'authz_by' => $this->limitString($authBy),
                'authc_status' => $authcStatus,
                'method' => $method,
                'timeout' => 0,
                'time_left' => null,
                'vlan' => $vlan,
                'time_elapsed' => $this->elapsedFromTimeLearned((int) ($row[$mib . 'TimeLearned'] ?? 0), $uptimeTicks),
            ]));
        }

        return $nac->each(function (PortsNac $entry) use ($authenticatedMacs): void {
            if (count($authenticatedMacs[$entry->port_id] ?? []) > 1) {
                $entry->host_mode = 'multiAuth';
            }
        });
    }

    public function discoverTransceivers(): Collection
    {
        // Only fetch the identity and mapping columns needed for transceivers.
        $entities = SnmpQuery::walk([
            'ENTITY-MIB::entPhysicalContainedIn',
            'ENTITY-MIB::entPhysicalParentRelPos',
            'ENTITY-MIB::entPhysicalName',
            'ENTITY-MIB::entPhysicalSerialNum',
            'ENTITY-MIB::entPhysicalMfgName',
            'ENTITY-MIB::entPhysicalModelName',
            'ENTITY-MIB::entPhysicalClass',
            'ALCATEL-IND1-CHASSIS-MIB::chasEntPhysNiNum',
            'ALCATEL-IND1-CHASSIS-MIB::chasEntPhysGbicNum',
        ])->valuesByIndex();

        $slots = [];

        foreach ($entities as $entityIndex => $entity) {
            if (preg_match('/^NI-(\d+)$/', (string) ($entity['ENTITY-MIB::entPhysicalName'] ?? ''), $matches)) {
                $slots[$entityIndex] = (int) $matches[1];
            }
        }

        $entitiesByIfIndex = [];
        $hasPortMapping = collect($entities)->contains(fn ($entity) => (int) ($entity['ALCATEL-IND1-CHASSIS-MIB::chasEntPhysNiNum'] ?? 0) > 0 &&
            (int) ($entity['ALCATEL-IND1-CHASSIS-MIB::chasEntPhysGbicNum'] ?? 0) > 0
        );

        foreach ($entities as $entityIndex => $entity) {
            $slot = (int) ($entity['ALCATEL-IND1-CHASSIS-MIB::chasEntPhysNiNum'] ?? 0);
            $port = (int) ($entity['ALCATEL-IND1-CHASSIS-MIB::chasEntPhysGbicNum'] ?? 0);

            if (! $hasPortMapping) {
                // Older agents may omit the vendor mapping. Expansion cards also have class port(10),
                // but their OSxxxx model identifies the card rather than a pluggable transceiver.
                if ((int) ($entity['ENTITY-MIB::entPhysicalClass'] ?? 0) !== 10 ||
                    preg_match('/^OS\d/', (string) ($entity['ENTITY-MIB::entPhysicalModelName'] ?? ''))) {
                    continue;
                }

                $parent = (int) ($entity['ENTITY-MIB::entPhysicalContainedIn'] ?? 0);
                $slot = $slots[$parent] ?? 0;
                $port = (int) ($entity['ENTITY-MIB::entPhysicalParentRelPos'] ?? 0);
            }

            if ($slot > 0 && $port > 0) {
                $entitiesByIfIndex[($slot * 1000) + $port] = ['index' => $entityIndex, 'entity' => $entity];
            }
        }

        $transceivers = collect();

        foreach (SnmpQuery::walk('ALCATEL-IND1-PORT-MIB::ddmSupplyVoltage')->valuesByIndex() as $index => $ddm) {
            /*
             * Same presence test as the DDM sensors: an empty cage reports no supply voltage.
             * Tx bias cannot be used, it is also zero for installed modules without a laser (copper, DAC).
             */
            if ((int) ($ddm['ALCATEL-IND1-PORT-MIB::ddmSupplyVoltage'] ?? 0) <= 0) {
                continue;
            }

            $ifIndex = (int) $index;
            $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId());

            if (! $portId) {
                continue;
            }

            $mappedEntity = $entitiesByIfIndex[$ifIndex] ?? [];
            $entity = $mappedEntity['entity'] ?? [];
            $part = $entity['ENTITY-MIB::entPhysicalModelName'] ?? null;

            $transceivers->push(new Transceiver([
                'port_id' => $portId,
                'index' => $ifIndex,
                'type' => ! empty($part) && $part !== 'OEM' ? $part : 'SFP/Transceiver',
                'vendor' => $entity['ENTITY-MIB::entPhysicalMfgName'] ?? null,
                'model' => $part,
                'serial' => $entity['ENTITY-MIB::entPhysicalSerialNum'] ?? null,
                'revision' => null,
                // AOS6 DDM sensors use ifIndex as entPhysicalIndex (measured against ports).
                'entity_physical_index' => $ifIndex,
                'ddm' => 1,
            ]));
        }

        return $transceivers;
    }

    /**
     * AOS6 exposes configured RADIUS servers, not the server used by each session.
     */
    private function getNacRadiusServers(string $tableOid, array $serverOids): string
    {
        $table = collect(
            SnmpQuery::mibDir('nokia/aos6')
                ->walk($tableOid)
                ->valuesByIndex()
        );

        if ($table->isEmpty()) {
            return 'RADIUS';
        }

        $row = (array) $table->first();
        $servers = [];

        foreach ($serverOids as $oid) {
            $server = trim((string) ($row[$oid] ?? ''));

            if ($server !== '') {
                $servers[] = $server;
            }
        }

        if (empty($servers)) {
            return 'RADIUS';
        }

        return $this->limitString(implode(',', array_unique($servers)));
    }

    /**
     * AOS6 can return IPv4 as dotted notation or an unsigned 32-bit integer.
     */
    private function normalizeIpAddress(mixed $value): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        $value = trim((string) ($value ?? ''));

        if ($value === '' || $value === '0' || $value === '0.0.0.0') {
            return '0.0.0.0';
        }

        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $value;
        }

        if (ctype_digit($value)) {
            $integer = (int) $value;

            if ($integer > 0 && $integer <= 4294967295) {
                $ip = inet_ntop(pack('N', $integer));

                if ($ip !== false) {
                    return $ip;
                }
            }
        }

        return $value;
    }

    /**
     * The MIB declares TimeStamp, but AOS 6.4.6 also reports Unix seconds.
     * Accept epoch values within this boot; otherwise interpret the value as uptime ticks.
     */
    private function elapsedFromTimeLearned(int $learned, int $uptimeTicks): ?int
    {
        $uptime = (int) $this->getDevice()->uptime;

        if ($learned <= 0 || $uptime <= 0) {
            return null;
        }

        $now = now()->timestamp;
        if ($learned >= $now - $uptime && $learned <= $now) {
            return $now - $learned;
        }

        // TimeStamp is relative to the SNMP agent, which may have restarted since the device booted.
        if ($uptimeTicks <= 0 || $learned > $uptimeTicks) {
            return null;
        }

        return intdiv($uptimeTicks - $learned, 100);
    }

    private function limitString(string $value): string
    {
        return substr($value, 0, self::PORTS_NAC_STR_MAX);
    }
}
