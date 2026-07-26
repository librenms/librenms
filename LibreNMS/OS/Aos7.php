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

            // Handle IP Address parsing (AOS7 often sends Hex-String, but can send other formats)
            $ipRaw = (string) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserIpAddress') ?? '');
            $ip = '0.0.0.0'; // Default to prevent DB errors

            // 1. Try Hex String decoding (Common for AOS7/8) e.g. "0A 0B 0C 0D"
            $hexDecoded = $this->decodeHexIp($ipRaw);
            if ($hexDecoded) {
                $ip = $hexDecoded;
            }
            // 2. If it was already a valid IP string
            elseif (filter_var($ipRaw, FILTER_VALIDATE_IP)) {
                $ip = $ipRaw;
            }
            // 3. Fallback for Integer format (Common for AOS6, rare for AOS7 but safer to have)
            elseif (is_numeric($ipRaw) && $ipRaw > 0) {
                $converted = long2ip((int) $ipRaw);
                if ($converted) {
                    $ip = $converted;
                }
            }

            // UNP details:
            //  - UnpUsed = what the switch actually applied for classification
            //  - UnpFromAuthServer = what RADIUS returned (Filter-ID / UNP profile)
            $unpUsed = $this->normalizeAosDaString(
                (string) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserUnpUsed') ?? '')
            );
            $unpFromAuthServer = $this->normalizeAosDaString(
                (string) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserUnpFromAuthServer') ?? '')
            );

            $authSrv = (string) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserAuthServerUsed') ?? '');
            $srvMsg = (string) ($this->rowValue($row, 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserServerMessage') ?? '');
            $srvMsgTrim = trim((string) $srvMsg);

            $method = match ($authtype) {
                0 => 'mab',
                2 => 'dot1x',
                default => 'unknown',
            };

            $authId = sprintf('%d-%s-%d', $ifIndex, $macNoSep, (int) $vlan);
            $timeElapsed = $this->parseAosTimestamp($loginTs);

            $authcStatus = $this->mapAosAuthcStatus($authStatus);

            // Base authz mapping
            $authzStatus = $this->mapAosAuthzStatus($authStatus);

            // IMPORTANT FIX:
            // AOS UNP can report "authenticated(3)" while still blocking traffic if the RADIUS UNP (Filter-ID)
            // isn't actually applied (UnpUsed empty) or is mismatched. In that case, authz must NOT be success.
            $authzStatus = $this->adjustAosAuthzStatusForUnp($authzStatus, $authStatus, $unpUsed, $unpFromAuthServer);

            // If NAC indicates "blocked/deny/quarantine" in server message, treat as authzFail (regardless of reason)
            $authzStatus = $this->adjustAosAuthzStatusForServerMessage($authzStatus, $srvMsgTrim);

            $hostMode = (($authenticatedCountByIfIndex[$ifIndex] ?? 0) > 1) ? 'multiAuth' : 'singleHost';

            $timeoutValue = 0;
            if ($method === 'dot1x') {
                $timeoutValue = $portTimeoutByIfIndex[$ifIndex] ?? 0;
            }

            // IMPORTANT for your DB schema: authz_by MUST NOT be null/empty
            $authzBy = ($authSrv !== '' && $authSrv !== '-') ? $authSrv : 'RADIUS';

            // Domain/Profile shown in GUI: prefer what the switch actually used; else show what RADIUS returned.
            $domain = $unpUsed !== '' ? $unpUsed : ($unpFromAuthServer !== '' ? $unpFromAuthServer : 'UNP');

            $entry = new PortsNac([
                'port_id' => $portId,
                'auth_id' => $authId,
                'domain' => $domain,
                'username' => $username,
                'mac_address' => $macNoSep,
                'ip_address' => $ip,
                'host_mode' => $hostMode,
                'authz_status' => $authzStatus,
                'authz_by' => $authzBy,
                'authc_status' => $authcStatus,
                'method' => $method,
                'timeout' => $timeoutValue,
                'time_left' => null,
                'vlan' => (int) $vlan,
                'time_elapsed' => $timeElapsed > 0 ? $timeElapsed : null,
            ]);

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
     * Helper to decode Hex-String IP (e.g. "0A 01 02 03") to "10.1.2.3"
     */
    private function decodeHexIp(string $hex): ?string
    {
        // Clean up quotes and whitespace
        $hex = trim($hex, " \"'");

        if ($hex === '') {
            return null;
        }

        // Match exactly 4 groups of 1-2 hex digits
        // This handles "0a 0b 0c 0d", "0a:0b:0c:0d", "0a0b0c0d"
        preg_match_all('/([0-9a-fA-F]{1,2})/', $hex, $matches);

        // We specifically want IPv4 (4 octets)
        // If we get 4 matches that look like bytes, assume it's an IP
        if (! empty($matches[1]) && count($matches[1]) >= 4) {
            // Take the first 4 bytes found
            $bytes = array_slice($matches[1], 0, 4);
            $octets = array_map(hexdec(...), $bytes);

            return implode('.', $octets);
        }

        return null;
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
     * [column][ifIndex][mac][vlan] => value
     * into:
     * ["ifIndex.mac.vlan"] => [column => value, shortColumn => value, ...]
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

    /**
     * Adjust authorization status based on UNP enforcement details.
     */
    private function adjustAosAuthzStatusForUnp(string $authzStatus, int $authStatus, string $unpUsed, string $unpFromAuthServer): string
    {
        if ($authStatus !== 3 || $authzStatus !== 'authzSuccess') {
            return $authzStatus;
        }

        // If RADIUS returned a profile but the switch didn't apply it -> blocked/mismatch
        if ($unpFromAuthServer !== '' && ($unpUsed === '' || strcasecmp($unpFromAuthServer, $unpUsed) !== 0)) {
            return 'authzFail';
        }

        return $authzStatus;
    }

    /**
     * If NAC indicates blocking in the server message, force authzFail.
     */
    private function adjustAosAuthzStatusForServerMessage(string $authzStatus, string $srvMsgTrim): string
    {
        if ($srvMsgTrim === '' || $srvMsgTrim === '-') {
            return $authzStatus;
        }

        $m = strtolower($srvMsgTrim);

        if (
            str_contains($m, 'block') ||
            str_contains($m, 'deny') ||
            str_contains($m, 'reject') ||
            str_contains($m, 'unauthor') ||
            str_contains($m, 'quarantine') ||
            str_contains($m, 'critical')
        ) {
            return 'authzFail';
        }

        return $authzStatus;
    }

    /**
     * Normalize AOS DA string values.
     */
    private function normalizeAosDaString(string $value): string
    {
        $v = trim($value);
        $v = trim($v, "\"'");

        return ($v === '' || $v === '-') ? '' : $v;
    }

    /**
     * Parse AOS DA index forms.
     */
    private function parseAosDaIndex(string $index): array
    {
        $parts = explode('.', $index);

        // Most common: "<ifIndex>.<mac>.<vlan>"
        if (count($parts) === 3) {
            $ifIndex = (int) $parts[0];
            $macRaw = (string) $parts[1];
            $vlanRaw = $parts[2];

            if ($ifIndex <= 0 || ! is_numeric($vlanRaw)) {
                return [0, '', '', null];
            }

            $macColon = $this->normalizeMacColon($macRaw);
            $macNoSep = $this->normalizeMacNoSep($macColon);
            $vlan = (int) $vlanRaw;

            return [$ifIndex, $macColon, $macNoSep, $vlan];
        }

        // Numeric-octet style: "<ifIndex>.<b1>.<b2>.<b3>.<b4>.<b5>.<b6>.<vlan>"
        if (count($parts) >= 8) {
            $ifIndex = (int) $parts[0];
            $macBytes = array_slice($parts, 1, 6);
            $vlanPart = $parts[7];

            if ($ifIndex <= 0 || ! is_numeric($vlanPart)) {
                return [0, '', '', null];
            }

            $macColon = implode(':', array_map(
                static fn ($b) => str_pad(dechex((int) $b), 2, '0', STR_PAD_LEFT),
                $macBytes
            ));

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

        $parts = $this->normalizeMacIndexGroups($parts);

        return implode(':', $parts);
    }

    private function normalizeMacIndexGroups(array $parts): array
    {
        return array_map(static function ($p) {
            $p = strtolower(trim((string) $p));
            $p = preg_replace('/[^0-9a-f]/', '', $p) ?? '';

            return str_pad($p, 2, '0', STR_PAD_LEFT);
        }, $parts);
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
            if (! empty($port->ifName) && preg_match('/^(\d+)\/(\d+)\/(\d+)[A-Z]?$/', (string) $port->ifName, $matches)) {
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
