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

        $successCountByIfIndex = [];

        foreach ($rows as $row) {
            $slot = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusSlotNumber') ?? 0);
            $port = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusPortNumber') ?? 0);

            if ($slot <= 0 || $port <= 0) {
                continue;
            }

            $authResult = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusAuthResult') ?? 0);

            if ($authResult === 2) {
                $ifIndex = ($slot * 1000) + $port;
                $successCountByIfIndex[$ifIndex] = ($successCountByIfIndex[$ifIndex] ?? 0) + 1;
            }
        }

        $nac = collect();

        foreach ($rows as $row) {
            $slot = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusSlotNumber') ?? 0);
            $port = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusPortNumber') ?? 0);

            if ($slot <= 0 || $port <= 0) {
                continue;
            }

            // AOS6 physical ifIndex is slot * 1000 + port.
            $ifIndex = ($slot * 1000) + $port;
            $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId());

            if (! $portId) {
                continue;
            }

            [$macColon, $macNoSep] = $this->normalizeMac(
                (string) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusMACAddress') ?? '')
            );

            if ($macNoSep === '') {
                continue;
            }

            $vlan = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusVlan') ?? 0);

            $profile = trim((string) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusProfileUsed'
            ) ?? ''));

            $domain = ($profile !== '' && $profile !== '--') ? $profile : 'UNP';

            $username = trim((string) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusUserName'
            ) ?? ''));

            if ($username === '' || $username === '--') {
                $username = $macColon !== '' ? $macColon : $macNoSep;
            }

            $authType = (int) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusAuthType'
            ) ?? 0);

            $method = match ($authType) {
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

            $authResult = (int) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusAuthResult'
            ) ?? 0);

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

            $timeLearned = (int) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusTimeLearned'
            ) ?? 0);

            $timeElapsed = $this->elapsedFromUnix($timeLearned);

            $nac->push(new PortsNac([
                'port_id' => $portId,
                'auth_id' => sprintf('%d-%s-%d', $ifIndex, $macNoSep, $vlan),
                'domain' => $this->limitString($domain),
                'username' => $this->limitString($username),
                'mac_address' => $macNoSep,
                'ip_address' => $this->normalizeIpAddress(
                    $this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusIPAddress')
                ),
                'host_mode' => (($successCountByIfIndex[$ifIndex] ?? 0) > 1) ? 'multiAuth' : 'singleHost',
                'authz_status' => $authzStatus,
                'authz_by' => $this->limitString($authBy),
                'authc_status' => $authcStatus,
                'method' => $method,
                'timeout' => 0,
                'time_left' => null,
                'vlan' => $vlan,
                'time_elapsed' => $timeElapsed ?: null,
            ]));
        }

        return $nac;
    }

    public function discoverTransceivers(): Collection
    {
        $ports = $this->getDevice()->ports()->get()->keyBy('ifIndex');

        $entities = collect(
            SnmpQuery::walk('ENTITY-MIB::entPhysicalEntry')
                ->valuesByIndex()
        );

        $slots = [];

        foreach ($entities as $entityIndex => $entity) {
            if (preg_match('/^NI-(\d+)$/', (string) ($entity['entPhysicalName'] ?? ''), $matches)) {
                $slots[$entityIndex] = (int) $matches[1];
            }
        }

        $entitiesByIfIndex = [];

        foreach ($entities as $entity) {
            $parent = (int) ($entity['entPhysicalContainedIn'] ?? 0);
            $port = (int) ($entity['entPhysicalParentRelPos'] ?? 0);

            if ($port > 0 && isset($slots[$parent])) {
                $entitiesByIfIndex[($slots[$parent] * 1000) + $port] = $entity;
            }
        }

        $transceivers = collect();

        foreach (SnmpQuery::walk('ALCATEL-IND1-PORT-MIB::ddmTxBiasCurrent')->valuesByIndex() as $index => $bias) {
            /*
             * Optical modules have Tx bias even when Rx is absent.
             * Copper and empty ports report zero.
             */
            if ((int) $bias <= 0) {
                continue;
            }

            $ifIndex = (int) $index;
            $port = $ports->get($ifIndex);

            if (! $port) {
                continue;
            }

            $entity = $entitiesByIfIndex[$ifIndex] ?? [];
            $part = $entity['entPhysicalModelName'] ?? null;

            $transceivers->push(new Transceiver([
                'port_id' => $port->port_id,
                'index' => $ifIndex,
                'type' => ! empty($part) && $part !== 'OEM' ? $part : 'SFP/Transceiver',
                'vendor' => $entity['entPhysicalMfgName'] ?? null,
                'part_number' => $part,
                'serial' => $entity['entPhysicalSerialNum'] ?? null,
                'revision' => null,
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
            $server = trim((string) ($this->rowValue($row, $oid) ?? ''));

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
     * Normalize AOS6 MAC values to colon-separated and database formats.
     */
    private function normalizeMac(string $mac): array
    {
        $mac = strtolower(trim($mac));

        if ($mac === '') {
            return ['', ''];
        }

        $parts = preg_split('/[:-]/', $mac);

        if ($parts && count($parts) === 6) {
            $parts = array_map(
                static fn ($part) => str_pad($part, 2, '0', STR_PAD_LEFT),
                $parts
            );

            $mac = implode(':', $parts);
        }

        return [$mac, str_replace([':', '-'], '', $mac)];
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

    private function elapsedFromUnix(int $timestamp): int
    {
        if ($timestamp < 946684800 || $timestamp > time()) {
            return 0;
        }

        return time() - $timestamp;
    }

    private function limitString(string $value): string
    {
        return substr($value, 0, self::PORTS_NAC_STR_MAX);
    }

    private function rowValue(mixed $row, string $oid): mixed
    {
        if (! is_array($row)) {
            return null;
        }

        if (array_key_exists($oid, $row)) {
            return $row[$oid];
        }

        $shortOid = preg_replace('/^.*::/', '', $oid);

        return $row[$shortOid] ?? null;
    }
}
