<?php

/**
 * Aos7.php
 *
 * -Description-
 * 
 * Base Alcatel-Lucent OS (AOS7/AOS8)
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
 * @link        https://www.librenms.org
 *
 * @copyright   2026 Peca Nesovanovic
 * @copyright   2026 Tony Murray
 * @author      Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 * @author      Tony Murray <murraytony@gmail.com>
 * @author      Paul Iercosan <mail@paulierco.ro>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\PortsNac;
use App\Models\PortVlan;
use App\Models\Transceiver;
use App\Models\Vlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Discovery\VlanDiscovery;
use LibreNMS\Interfaces\Discovery\VlanPortDiscovery;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\OS;
use SnmpQuery;

class Aos7 extends OS implements VlanDiscovery, VlanPortDiscovery, TransceiverDiscovery, NacPolling
{
    /**
     * Poll NAC sessions for AOS7/AOS8 using ALCATEL-IND1-DA-MIB.
     */
    public function pollNac(): Collection
    {
        $nac = collect();

        // Single table walk
        $raw = SnmpQuery::mibDir('nokia/aos7')
            ->walk('ALCATEL-IND1-DA-MIB::alaDaMacVlanUserTable')
            ->valuesByIndex();

        $rows = collect($raw);

        if ($rows->isEmpty()) {
            return $nac;
        }

        // Some AOS7 return COLUMN-oriented tables:
        //   alaDaMacVlanUserAuthStatus[ifIndex][mac][vlan] = X
        // In that case $rows is keyed by column name. Flatten it to row-oriented:
        //   "<ifIndex>.<mac>.<vlan>" => [column => value, ...]
        if ($this->isColumnOrientedDaTable($raw)) {
            $rows = collect($this->flattenColumnOrientedDaTable($raw));
        }

        if ($rows->isEmpty()) {
            return $nac;
        }

        // Port-level 802.1X supplicant timeout
        $portTimeoutRows = collect(
            SnmpQuery::mibDir('nokia/aos7')
                ->walk('ALCATEL-IND1-DA-MIB::alaDaUNPPort8021XSuppTimeOut')
                ->valuesByIndex()
        );

        $portTimeoutByIfIndex = [];
        foreach ($portTimeoutRows as $idx => $val) {
            $ifIndex = (int) $idx;
            if ($ifIndex <= 0) {
                continue;
            }

            $timeout = is_array($val)
                ? ($val['ALCATEL-IND1-DA-MIB::alaDaUNPPort8021XSuppTimeOut'] ?? $val['alaDaUNPPort8021XSuppTimeOut'] ?? null)
                : $val;

            if ($timeout !== null && is_numeric($timeout)) {
                $portTimeoutByIfIndex[$ifIndex] = (int) $timeout;
            }
        }

        // Infer host_mode per ifIndex based on number of AUTHENTICATED sessions
        $authenticatedCountByIfIndex = [];
        foreach ($rows as $index => $row) {
            [$ifIndex, , , $vlan] = $this->parseAosDaIndex((string) $index);
            if (! $ifIndex || $vlan === null) {
                continue;
            }

            $authStatus = (int) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserAuthStatus') ?? 0);
            if ($authStatus === 3) { // authenticated(3)
                $authenticatedCountByIfIndex[$ifIndex] = ($authenticatedCountByIfIndex[$ifIndex] ?? 0) + 1;
            }
        }

        foreach ($rows as $index => $row) {
            [$ifIndex, $macColon, $macNoSep, $vlan] = $this->parseAosDaIndex((string) $index);
            if (! $ifIndex || ! $macNoSep || $vlan === null) {
                continue;
            }

            $authStatus = (int) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserAuthStatus') ?? 0);
            if ($authStatus === 1) { // idle(1)
                continue;
            }

            $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId());
            if (! $portId) {
                continue;
            }

            $username = (string) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserName') ?? $macColon);
            $authtype = (int) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserAuthtype') ?? 0);
            $loginTs = (string) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserLoginTimeStamp') ?? '');
            $ipHex = (string) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserIpAddress') ?? '');
            $profile = (string) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserUnpUsed') ?? '');
            $authSrv = (string) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserAuthServerUsed') ?? '');
            $srvMsg = (string) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserServerMessage') ?? '');

            $method = match ($authtype) {
                0 => 'mab',
                2 => 'dot1x',
                default => 'unknown',
            };

            $authId = sprintf('%d-%s-%d', $ifIndex, $macNoSep, (int) $vlan);
            $timeElapsed = $this->parseAosTimestamp($loginTs);
            $ip = $this->decodeHexIp($ipHex);

            $authcStatus = $this->mapAosAuthcStatus($authStatus);
            $authzStatus = $this->mapAosAuthzStatus($authStatus);
            $hostMode = (($authenticatedCountByIfIndex[$ifIndex] ?? 0) > 1) ? 'multiAuth' : 'singleHost';

            $timeoutValue = 0;
            if ($method === 'dot1x') {
                $timeoutValue = $portTimeoutByIfIndex[$ifIndex] ?? 0;
            }

            // IMPORTANT for your DB schema: authz_by MUST NOT be null/empty
            $authzBy = ($authSrv !== '' && $authSrv !== '-') ? $authSrv : 'RADIUS';

            $entry = new PortsNac([
                'port_id'      => $portId,
                'auth_id'      => $authId,
                'domain'       => ($profile !== '' && $profile !== '-') ? $profile : 'UNP',
                'username'     => $username,
                'mac_address'  => $macNoSep,
                'ip_address'   => $ip,
                'host_mode'    => $hostMode,
                'authz_status' => $authzStatus,
                'authz_by'     => $authzBy,
                'authc_status' => $authcStatus,
                'method'       => $method,
                'timeout'      => $timeoutValue,
                'time_left'    => null,
                'vlan'         => (int) $vlan,
                'time_elapsed' => $timeElapsed > 0 ? $timeElapsed : null,
            ]);

            $srvMsgTrim = trim((string) $srvMsg);
            if ($srvMsgTrim !== '' && $srvMsgTrim !== '-') {
                $cacheKey = 'nac_srvmsg:' . $this->getDeviceId() . ':' . $authId;
                $prevMsg = Cache::get($cacheKey);

                if ($prevMsg !== $srvMsgTrim) {
                    if (function_exists('log_event')) {
                        log_event(
                            "NAC server message ($authId) on port_id=$portId: $srvMsgTrim",
                            $this->getDeviceId(),
                            'nac',
                            $portId,
                            3
                        );
                    }
                    Cache::put($cacheKey, $srvMsgTrim, now()->addDays(7));
                }
            }

            $nac->push($entry);
        }

        return $nac;
    }

    /**
     * Detect COLUMN-oriented DA table:
     * top-level keys look like alaDaMacVlanUser* columns.
     */
    private function isColumnOrientedDaTable(array $raw): bool
    {
        if (empty($raw)) {
            return false;
        }

        $firstKey = array_key_first($raw);
        if (! is_string($firstKey)) {
            return false;
        }

        return str_contains($firstKey, 'alaDaMacVlanUser') || str_contains($firstKey, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUser');
    }

    /**
     * Flatten COLUMN-oriented structure:
     *   [column][ifIndex][mac][vlan] => value
     * into:
     *   ["ifIndex.mac.vlan"] => [column => value, shortColumn => value, ...]
     */
    private function flattenColumnOrientedDaTable(array $raw): array
    {
        $flat = [];

        foreach ($raw as $col => $lvl1) {
            if (! is_array($lvl1)) {
                continue;
            }

            $short = is_string($col) ? preg_replace('/^.*::/', '', $col) : $col;

            foreach ($lvl1 as $ifIndex => $lvl2) {
                if (! is_array($lvl2)) {
                    continue;
                }

                foreach ($lvl2 as $mac => $lvl3) {
                    if (! is_array($lvl3)) {
                        continue;
                    }

                    foreach ($lvl3 as $vlan => $value) {
                        $idx = sprintf('%s.%s.%s', $ifIndex, $mac, $vlan);

                        if (! isset($flat[$idx])) {
                            $flat[$idx] = [];
                        }

                        // store both long and short key, so rowValue() finds it
                        $flat[$idx][$col] = $value;
                        if (is_string($short)) {
                            $flat[$idx][$short] = $value;
                        }
                    }
                }
            }
        }

        return $flat;
    }

    private function mapAosAuthcStatus(int $authStatus): string
    {
        return match ($authStatus) {
            3 => 'authcSuccess',
            2 => 'authcInProgress',
            1 => 'authcIdle',
            4, 5, 6, 7 => 'authcFail',
            default => 'authcUnknown',
        };
    }

    private function mapAosAuthzStatus(int $authStatus): string
    {
        return match ($authStatus) {
            3 => 'authzSuccess',
            2 => 'authzInProgress',
            1 => 'authzIdle',
            4, 5, 6, 7 => 'authzFail',
            default => 'authzUnknown',
        };
    }

    private function parseAosDaIndex(string $index): array
    {
        $parts = explode('.', $index);

        if (count($parts) === 3) {
            $ifIndex = (int) $parts[0];
            $macColon = $this->normalizeMacColon($parts[1]);
            $macNoSep = $this->normalizeMacNoSep($macColon);
            $vlan = (int) $parts[2];

            return [$ifIndex, $macColon, $macNoSep, $vlan];
        }

        if (count($parts) >= 8) {
            $ifIndex = (int) $parts[0];
            $macBytes = array_slice($parts, 1, 6);
            $vlanPart = $parts[7];
            if ($ifIndex <= 0 || ! is_numeric($vlanPart)) {
                return [0, '', '', null];
            }

            $macColon = implode(':', array_map(function ($b) {
                return str_pad(dechex((int) $b), 2, '0', STR_PAD_LEFT);
            }, $macBytes));

            $macColon = strtolower($macColon);
            $macNoSep = $this->normalizeMacNoSep($macColon);
            $vlan = (int) $vlanPart;

            return [$ifIndex, $macColon, $macNoSep, $vlan];
        }

        return [0, '', '', null];
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
        return strtolower(str_replace(':', '', trim($macColon)));
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

    private function parseAosTimestamp(string $timestamp): int
    {
        $timestamp = trim($timestamp);
        if (preg_match('/(\d+)-(\d+)-(\d+),(\d+):(\d+):(\d+)/', $timestamp, $m)) {
            $date = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $m[1], $m[2], $m[3], $m[4], $m[5], $m[6]);
            $startTime = strtotime($date);

            return $startTime ? (time() - $startTime) : 0;
        }

        return 0;
    }

    private function decodeHexIp(string $hex): ?string
    {
        $hex = trim($hex);
        $hex = trim($hex, "\"'");
        preg_match_all('/\b[0-9a-fA-F]{2}\b/', $hex, $m);
        $bytes = $m[0];

        if (count($bytes) === 4) {
            $octets = array_map(fn ($b) => hexdec($b), $bytes);

            return implode('.', $octets);
        }

        return null;
    }

    public function discoverVlans(): Collection
    {
        if (($QBridgeMibVlans = parent::discoverVlans())->isNotEmpty()) {
            return $QBridgeMibVlans;
        }

        return SnmpQuery::mibDir('nokia/aos7')
            ->walk('ALCATEL-IND1-VLAN-MGR-MIB::vlanDescription')
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

        return SnmpQuery::mibDir('nokia/aos7')
            ->walk('ALCATEL-IND1-VLAN-MGR-MIB::vpaType')
            ->mapTable(function ($data, $vpaVlanNumber, $vpaIfIndex) {
                $baseport = $this->bridgePortFromIfIndex($vpaIfIndex);
                if (! $baseport) {
                    return null;
                }

                return new PortVlan([
                    'vlan' => $vpaVlanNumber,
                    'baseport' => $baseport,
                    'untagged' => $data['ALCATEL-IND1-VLAN-MGR-MIB::vpaType'] === '1' ? 1 : 0,
                    'port_id' => PortCache::getIdFromIfIndex($vpaIfIndex, $this->getDeviceId()) ?? 0,
                ]);
            })->filter();
    }

    public function discoverTransceivers(): Collection
    {
        $device = $this->getDevice();
        $transceivers = new Collection();
        $portsKeyed = $device->ports()->get()->keyBy('ifIndex');

        $ddm_data = SnmpQuery::mibDir('nokia/aos7')
            ->walk('ALCATEL-IND1-PORT-MIB::ddmPortRxOpticalPower')
            ->valuesByIndex();

        $entities = $device->entityPhysical()->where('entPhysicalName', 'like', '%TRANSCEIVER%')->get();

        foreach ($ddm_data as $index => $entry) {
            $value = is_array($entry) ? ($entry['ALCATEL-IND1-PORT-MIB::ddmPortRxOpticalPower'] ?? -2147483648) : $entry;
            if ($value <= -2147483648) {
                continue;
            }

            $parts = explode('.', (string) $index);
            $ifIndex = (int) $parts[0];
            if ($ifIndex <= 0) {
                continue;
            }

            $port = $portsKeyed->get($ifIndex);
            if (! $port) {
                continue;
            }

            $match_entity = null;
            if (! empty($port->ifName) && preg_match('/^(\d+)\/(\d+)\/(\d+)[A-Z]?$/', $port->ifName, $matches)) {
                $expectedName = "{$matches[1]}/SLOT-{$matches[2]} TRANSCEIVER-{$matches[3]}";
                $match_entity = $entities->first(fn ($item) => (string) $item->entPhysicalName === $expectedName);
            }

            $transceivers->push(new Transceiver([
                'port_id' => $port->port_id,
                'index' => $ifIndex,
                'entity_physical_index' => $ifIndex,
                'type' => $match_entity ? $this->cleanStr($match_entity->entPhysicalDescr) : 'SFP',
                'vendor' => $match_entity ? $this->cleanStr($match_entity->entPhysicalMfgName) : null,
                'model' => $match_entity ? $this->cleanStr($match_entity->entPhysicalModelName) : null,
                'serial' => $match_entity ? $this->cleanStr($match_entity->entPhysicalSerialNum) : null,
                'revision' => $match_entity ? $this->cleanStr($match_entity->entPhysicalHardwareRev) : null,
                'ddm' => 1,
            ]));
        }

        return $transceivers;
    }

    private function cleanStr($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);

        return $s === '' ? null : $s;
    }
}
