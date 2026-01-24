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
            ->mapTable(function ($data, $vpaVlanNumber, $vpaIfIndex = null) {
                $portId = PortCache::getIdFromIfIndex($vpaIfIndex, $this->getDeviceId()) ?? 0;

                return new PortVlan([
                    'vlan' => $vpaVlanNumber,
                    'baseport' => $this->bridgePortFromIfIndex($vpaIfIndex),
                    'untagged' => ($data['ALCATEL-IND1-VLAN-MGR-MIB::vpaType'] == 1 ? 1 : 0),
                    'port_id' => $portId,
                ]);
            });
    }

    /**
     * Poll NAC sessions for AOS6 using ALCATEL-IND1-DOT1X-MIB.
     *
     * AOS6 does NOT provide per-session RADIUS server "used" (like AOS7/AOS8).
     * To populate authz_by, we read configured RADIUS servers from ALCATEL-IND1-AAA-MIB::aaas* (no secrets walked).
     *
     * IP address is often 0/empty on AOS6 for MAB; ports_nac.ip_address cannot be NULL, so we store 0.0.0.0.
     */
    public function pollNac(): Collection
    {
        $nac = collect();

        // Main per-device status table (contains supplicant + non-supplicant entries)
        $rows = collect(
            SnmpQuery::mibDir('nokia')
                ->walk('ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusTable')
                ->valuesByIndex()
        );

        if ($rows->isEmpty()) {
            return $nac;
        }

        // Infer host_mode by counting successful auth sessions per ifIndex
        $successCountByIfIndex = [];
        foreach ($rows as $index => $row) {
            $slot = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusSlotNumber') ?? 0);
            $port = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusPortNumber') ?? 0);
            if ($slot <= 0 || $port <= 0) {
                continue;
            }

            $ifIndex = ($slot * 1000) + $port;

            // ALADot1xAuthenticationResult: notApplicable(0), inProgress(1), success(2), fail(3)
            $authResult = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusAuthResult') ?? 0);
            if ($authResult === 2) {
                $successCountByIfIndex[$ifIndex] = ($successCountByIfIndex[$ifIndex] ?? 0) + 1;
            }
        }

        // Best-effort "Auth By" from configured RADIUS servers (not per-session)
        $authBy = $this->getConfiguredRadiusServersString() ?: 'RADIUS';

        foreach ($rows as $index => $row) {
            $slot = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusSlotNumber') ?? 0);
            $port = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusPortNumber') ?? 0);
            if ($slot <= 0 || $port <= 0) {
                continue;
            }

            $ifIndex = ($slot * 1000) + $port;

            $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId());
            if (! $portId) {
                continue;
            }

            $macColon = (string) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusMACAddress') ?? '');
            $macColon = $this->normalizeMacColon($macColon);
            $macNoSep = $this->normalizeMacNoSep($macColon);
            if ($macNoSep === '') {
                continue;
            }

            $vlan = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusVlan') ?? 0);

            $profile = trim((string) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusProfileUsed') ?? ''));
            $username = trim((string) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusUserName') ?? ''));
            if ($username === '' || $username === '--') {
                $username = $macColon !== '' ? $macColon : $macNoSep;
            }

            $ipRaw = $this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusIPAddress');
            $ip = $this->normalizeIpAddress($ipRaw);

            // Auth type: noAuthentication(0), dotXAuthentication(1), macAuthentication(2), captivePortal(3)
            $authType = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusAuthType') ?? 0);

            $method = match ($authType) {
                1 => 'dot1x',
                2 => 'mab',
                3 => 'captivePortal',
                default => 'unknown',
            };

            // Auth result: notApplicable(0), inProgress(1), success(2), fail(3)
            $authResult = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusAuthResult') ?? 0);
            $authcStatus = $this->mapAos6AuthcStatus($authResult);
            $authzStatus = $this->mapAos6AuthzStatus($authResult);

            $hostMode = (($successCountByIfIndex[$ifIndex] ?? 0) > 1) ? 'multiAuth' : 'singleHost';

            // Unix timestamp (seconds) when learned (often present on AOS6)
            $timeLearned = (int) ($this->rowValue($row, 'ALCATEL-IND1-DOT1X-MIB::alaDot1xDeviceStatusTimeLearned') ?? 0);
            $timeElapsed = $this->elapsedFromUnix($timeLearned);

            $authId = sprintf('%d-%s-%d', $ifIndex, $macNoSep, $vlan);

            $nac->push(new PortsNac([
                'port_id' => $portId,
                'auth_id' => $authId,
                'domain' => ($profile !== '' && $profile !== '--') ? $profile : 'UNP',
                'username' => $username,
                'mac_address' => $macNoSep,
                'ip_address' => $ip, // never null (DB constraint)
                'host_mode' => $hostMode,
                'authz_status' => $authzStatus,
                'authz_by' => $authBy,
                'authc_status' => $authcStatus,
                'method' => $method,
                'timeout' => 0,
                'time_left' => null,
                'vlan' => $vlan,
                'time_elapsed' => $timeElapsed > 0 ? $timeElapsed : null,
            ]));
        }

        return $nac;
    }

    /**
     * Discover Transceivers.
     *
     * Strategy:
     * - Use ddmTxBiasCurrent to detect Optical Transceivers.
     * - Copper SFPs report 0 Bias (no laser), so they are automatically filtered out.
     * - Fiber SFPs with a cut cable (0 Rx Power) still have valid Tx Bias, so they remain discovered.
     */
    public function discoverTransceivers(): Collection
    {
        $device = $this->getDevice();
        $transceivers = new Collection();

        // 1. Pre-fetch Ports
        $portsByIfIndex = $device->ports()->get()->keyBy('ifIndex');

        // 2. Fetch Entity Data for Metadata (Serial, Vendor, Model)
        // entPhysicalEntry is a full table, so it remains a nested array
        $entityData = collect(
            SnmpQuery::walk('ENTITY-MIB::entPhysicalEntry')
                ->valuesByIndex()
        );

        // 3. Build a Map: Parent Entity Index -> Slot Number
        // Look for "NI-1", "NI-2" to identify which entity represents which slot.
        $slotMap = [];
        foreach ($entityData as $eIndex => $entry) {
            $name = $entry['entPhysicalName'] ?? '';
            // Match "NI-1", "NI-2", etc.
            if (preg_match('/^NI-(\d+)$/', $name, $matches)) {
                $slotMap[$eIndex] = (int) $matches[1];
            }
        }

        // 4. Build a Map: Calculated ifIndex -> Entity Data
        // AOS6 ifIndex = (Slot * 1000) + Port
        $entityByIfIndex = [];
        foreach ($entityData as $eIndex => $entry) {
            $parentIndex = $entry['entPhysicalContainedIn'] ?? 0;
            $portNum = $entry['entPhysicalParentRelPos'] ?? 0;

            if ($parentIndex > 0 && $portNum > 0 && isset($slotMap[$parentIndex])) {
                $slot = $slotMap[$parentIndex];
                $calculatedIfIndex = ($slot * 1000) + $portNum;
                $entityByIfIndex[$calculatedIfIndex] = $entry;
            }
        }

        // 5. Fetch DDM Bias Current (Reliable indicator of Optics)
        // OID: .1.3.6.1.4.1.6486.800.1.2.1.5.1.1.2.5.1.11
        $ddmData = collect(
            SnmpQuery::walk('ALCATEL-IND1-PORT-MIB::ddmTxBiasCurrent')
                ->valuesByIndex()
        );

        foreach ($ddmData as $index => $biasValue) {
            // FIX: Since we walked a single column, $biasValue is now the actual integer, not an array.
            $biasValue = (int) $biasValue;

            // FILTER: If Bias is 0, it is Copper or Empty.
            // A fiber cut does NOT stop the laser bias, so real fiber stays.
            if ($biasValue <= 0) {
                continue;
            }

            $ifIndex = (int) $index;

            $port = $portsByIfIndex->get($ifIndex);
            if (! $port) {
                continue;
            }

            // Default values
            $vendor = null;
            $part = null;
            $serial = null;
            $type = 'SFP/Transceiver';

            // Try to find matching Entity Metadata
            if (isset($entityByIfIndex[$ifIndex])) {
                $e = $entityByIfIndex[$ifIndex];
                $vendor = $e['entPhysicalMfgName'] ?? null;
                $part = $e['entPhysicalModelName'] ?? null;
                $serial = $e['entPhysicalSerialNum'] ?? null;

                if (! empty($part) && $part !== 'OEM') {
                    $type = $part;
                }
            }

            $transceivers->push(new Transceiver([
                'port_id' => $port->port_id,
                'index' => $ifIndex,
                'type' => $type,
                'vendor' => $vendor,
                'part_number' => $part,
                'serial' => $serial,
                'revision' => null,
                'entity_physical_index' => $ifIndex,
                'ddm' => 1,
            ]));
        }

        return $transceivers;
    }

    private function mapAos6AuthcStatus(int $authResult): string
    {
        // notApplicable(0), inProgress(1), success(2), fail(3)
        return match ($authResult) {
            2 => 'authcSuccess',
            1 => 'authcInProgress',
            3 => 'authcFail',
            0 => 'authcUnknown',
            default => 'authcUnknown',
        };
    }

    private function mapAos6AuthzStatus(int $authResult): string
    {
        // notApplicable(0), inProgress(1), success(2), fail(3)
        return match ($authResult) {
            2 => 'authzSuccess',
            1 => 'authzInProgress',
            3 => 'authzFail',
            0 => 'authzUnknown',
            default => 'authzUnknown',
        };
    }

    /**
     * Best-effort: list configured RADIUS servers from ALCATEL-IND1-AAA-MIB.
     * We only walk non-sensitive columns (protocol, hostname, ip).
     */
    private function getConfiguredRadiusServersString(): string
    {
        $protoRows = collect(
            SnmpQuery::mibDir('nokia')
                ->walk('ALCATEL-IND1-AAA-MIB::aaasProtocol')
                ->valuesByIndex()
        );

        if ($protoRows->isEmpty()) {
            return '';
        }

        $hostRows = collect(
            SnmpQuery::mibDir('nokia')
                ->walk('ALCATEL-IND1-AAA-MIB::aaasHostName')
                ->valuesByIndex()
        );

        $ipRows = collect(
            SnmpQuery::mibDir('nokia')
                ->walk('ALCATEL-IND1-AAA-MIB::aaasIpAddress')
                ->valuesByIndex()
        );

        $names = [];
        foreach ($protoRows as $nameIdx => $val) {
            $proto = (int) ($this->rowValue($val, 'ALCATEL-IND1-AAA-MIB::aaasProtocol') ?? $val ?? 0);
            if ($proto !== 1) { // radius(1)
                continue;
            }

            $name = trim((string) $nameIdx);
            if ($name !== '') {
                $names[] = $name;
                continue;
            }

            // Fallback to host/ip if index name isn't usable
            $host = trim((string) ($this->rowValue($hostRows->get($nameIdx), 'ALCATEL-IND1-AAA-MIB::aaasHostName') ?? ''));
            $ip = $this->normalizeIpAddress($ipRows->get($nameIdx));

            if ($host !== '') {
                $names[] = $host;
            } elseif ($ip !== '') {
                $names[] = $ip;
            }
        }

        $names = array_values(array_unique(array_filter($names)));

        return implode(',', $names);
    }

    private function normalizeMacColon(string $mac): string
    {
        $mac = strtolower(trim($mac));
        $parts = preg_split('/[:\-]/', $mac);
        if (! $parts || count($parts) !== 6) {
            return $mac;
        }
        $parts = array_map(fn ($p) => str_pad($p, 2, '0', STR_PAD_LEFT), $parts);

        return implode(':', $parts);
    }

    private function normalizeMacNoSep(string $macColon): string
    {
        $macColon = strtolower(trim($macColon));
        if ($macColon === '') {
            return '';
        }

        return strtolower(str_replace(':', '', $macColon));
    }

    private function normalizeIpAddress($val): string
    {
        if ($val === null) {
            return '0.0.0.0';
        }

        if (is_array($val)) {
            // If it's an array (valuesByIndex format), try to pick first scalar-ish value
            $first = reset($val);
            $val = $first !== false ? $first : '';
        }

        $s = trim((string) $val);

        // AOS6 often returns "0" here; DB requires non-null.
        if ($s === '' || $s === '0' || $s === '0.0.0.0') {
            return '0.0.0.0';
        }

        return $s;
    }

    private function elapsedFromUnix(int $unixTs): int
    {
        if ($unixTs <= 0) {
            return 0;
        }

        // sanity: if it looks like a unix timestamp
        if ($unixTs < 946684800) { // < 2000-01-01
            return 0;
        }

        $now = time();
        if ($unixTs > $now) {
            return 0;
        }

        return $now - $unixTs;
    }

    private function rowValue($row, string $oid)
    {
        if (is_array($row) && array_key_exists($oid, $row)) {
            return $row[$oid];
        }
        $short = preg_replace('/^.*::/', '', $oid);
        if (is_array($row) && array_key_exists($short, $row)) {
            return $row[$short];
        }

        return null;
    }
}
