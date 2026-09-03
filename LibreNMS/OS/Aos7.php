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
    public function pollNac(): Collection
    {
        $nac = collect();

        $raw = SnmpQuery::mibDir('nokia/aos7')
            ->walk('ALCATEL-IND1-DA-MIB::alaDaMacVlanUserTable')
            ->valuesByIndex();

        $rows = collect($raw);
        if ($rows->isEmpty()) {
            return $nac;
        }

        if ($this->isColumnOrientedDaTable($raw)) {
            $rows = collect($this->flattenColumnOrientedDaTable($raw));
        }

        if ($rows->isEmpty()) {
            return $nac;
        }

        $portTimeoutRows = collect(
            SnmpQuery::mibDir('nokia/aos7')
                ->walk('ALCATEL-IND1-DA-MIB::alaDaUNPPort8021XSuppTimeOut')
                ->valuesByIndex()
        );

        $portTimeoutByIfIndex = [];

        foreach ($portTimeoutRows as $index => $value) {
            $ifIndex = (int) $index;

            if ($ifIndex <= 0) {
                continue;
            }

            $timeout = is_array($value)
                ? ($value['ALCATEL-IND1-DA-MIB::alaDaUNPPort8021XSuppTimeOut']
                    ?? $value['alaDaUNPPort8021XSuppTimeOut']
                    ?? null)
                : $value;

            if ($timeout !== null && is_numeric($timeout)) {
                $portTimeoutByIfIndex[$ifIndex] = (int) $timeout;
            }
        }

        $authenticatedCountByIfIndex = [];

        foreach ($rows as $index => $row) {
            [$ifIndex, , , $vlan] = $this->parseAosDaIndex((string) $index);

            if (! $ifIndex || $vlan === null) {
                continue;
            }

            $authStatus = (int) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserAuthStatus'
            ) ?? 0);

            if ($authStatus === 3) {
                $authenticatedCountByIfIndex[$ifIndex] =
                    ($authenticatedCountByIfIndex[$ifIndex] ?? 0) + 1;
            }
        }

        foreach ($rows as $index => $row) {
            [$ifIndex, $macColon, $macNoSep, $vlan] = $this->parseAosDaIndex((string) $index);

            if (! $ifIndex || ! $macNoSep || $vlan === null) {
                continue;
            }

            $authStatus = (int) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserAuthStatus'
            ) ?? 0);

            $classificationSource = (int) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserClassificationSource'
            ) ?? 0);

            $isNoMatchingUnpBlock = $classificationSource === 60;

            // AOS normally reports inactive users as idle(1), which we do not
            // need to retain. However, "No Matching UNP - Block" is also
            // reported as idle(1), so keep that entry visible in LibreNMS.
            if ($authStatus === 1 && ! $isNoMatchingUnpBlock) {
                continue;
            }

            $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId());

            if (! $portId) {
                continue;
            }

            $username = (string) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserName'
            ) ?? $macColon);

            $authType = (int) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserAuthtype'
            ) ?? 0);

            $loginTimestamp = (string) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserLoginTimeStamp'
            ) ?? '');

            $ipRaw = (string) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserIpAddress'
            ) ?? '');

            $ip = '0.0.0.0';

            if ($hexIp = $this->decodeHexIp($ipRaw)) {
                $ip = $hexIp;
            } elseif (filter_var($ipRaw, FILTER_VALIDATE_IP)) {
                $ip = $ipRaw;
            } elseif (is_numeric($ipRaw) && $ipRaw > 0) {
                $ip = long2ip((int) $ipRaw) ?: $ip;
            }

            $unpUsed = $this->normalizeAosDaString(
                (string) ($this->rowValue(
                    $row,
                    'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserUnpUsed'
                ) ?? '')
            );

            $unpFromAuthServer = $this->normalizeAosDaString(
                (string) ($this->rowValue(
                    $row,
                    'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserUnpFromAuthServer'
                ) ?? '')
            );

            $authServer = (string) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserAuthServerUsed'
            ) ?? '');

            $serverMessage = trim((string) ($this->rowValue(
                $row,
                'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserServerMessage'
            ) ?? ''));

            $method = match ($authType) {
                0 => 'mab',
                2 => 'dot1x',
                default => 'unknown',
            };

            $authId = sprintf('%d-%s-%d', $ifIndex, $macNoSep, (int) $vlan);
            $timeElapsed = $this->parseAosTimestamp($loginTimestamp);

            $authcStatus = $this->mapAosAuthcStatus($authStatus);
            $authzStatus = $this->mapAosAuthzStatus($authStatus);

            $authzStatus = $this->adjustAosAuthzStatusForUnp(
                $authzStatus,
                $authStatus,
                $unpUsed,
                $unpFromAuthServer
            );

            $authzStatus = $this->adjustAosAuthzStatusForServerMessage(
                $authzStatus,
                $serverMessage
            );

            if ($isNoMatchingUnpBlock) {
                $authzStatus = 'authzFail';
            }

            $hostMode = (($authenticatedCountByIfIndex[$ifIndex] ?? 0) > 1)
                ? 'multiAuth'
                : 'singleHost';

            $timeout = $method === 'dot1x'
                ? ($portTimeoutByIfIndex[$ifIndex] ?? 0)
                : 0;

            $authzBy = $isNoMatchingUnpBlock
                ? 'UNP'
                : (($authServer !== '' && $authServer !== '-')
                    ? $authServer
                    : 'RADIUS');

            $domain = $isNoMatchingUnpBlock
                ? 'No Matching UNP - Block'
                : ($unpUsed !== ''
                    ? $unpUsed
                    : ($unpFromAuthServer !== '' ? $unpFromAuthServer : 'UNP'));

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
                'timeout' => $timeout,
                'time_left' => null,
                'vlan' => (int) $vlan,
                'time_elapsed' => $timeElapsed > 0 ? $timeElapsed : null,
            ]);

            if ($serverMessage !== '' && $serverMessage !== '-') {
                $cacheKey = 'nac_srvmsg:' . $this->getDeviceId() . ':' . $authId;
                $previousMessage = Cache::get($cacheKey);

                if ($previousMessage !== $serverMessage) {
                    if (function_exists('log_event')) {
                        log_event(
                            "NAC server message ($authId) on port_id=$portId: $serverMessage",
                            $this->getDeviceId(),
                            'nac',
                            $portId,
                            3
                        );
                    }

                    Cache::put($cacheKey, $serverMessage, now()->addDays(7));
                }
            }

            $nac->push($entry);
        }

        return $nac;
    }

    private function decodeHexIp(string $hex): ?string
    {
        $hex = trim($hex, " \"'");

        if ($hex === '') {
            return null;
        }

        preg_match_all('/([0-9a-fA-F]{1,2})/', $hex, $matches);

        if (empty($matches[1]) || count($matches[1]) < 4) {
            return null;
        }

        return implode('.', array_map(
            hexdec(...),
            array_slice($matches[1], 0, 4)
        ));
    }

    private function isColumnOrientedDaTable(array $raw): bool
    {
        if (empty($raw)) {
            return false;
        }

        $firstKey = array_key_first($raw);

        if (! is_string($firstKey)) {
            return false;
        }

        return str_contains($firstKey, 'alaDaMacVlanUser');
    }

    private function flattenColumnOrientedDaTable(array $raw): array
    {
        $flat = [];

        foreach ($raw as $column => $interfaces) {
            if (! is_array($interfaces)) {
                continue;
            }

            $shortColumn = is_string($column)
                ? preg_replace('/^.*::/', '', $column)
                : $column;

            foreach ($interfaces as $ifIndex => $macs) {
                if (! is_array($macs)) {
                    continue;
                }

                foreach ($macs as $mac => $vlans) {
                    if (! is_array($vlans)) {
                        continue;
                    }

                    foreach ($vlans as $vlan => $value) {
                        $index = sprintf('%s.%s.%s', $ifIndex, $mac, $vlan);

                        $flat[$index][$column] = $value;

                        if (is_string($shortColumn)) {
                            $flat[$index][$shortColumn] = $value;
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

    private function adjustAosAuthzStatusForUnp(
        string $authzStatus,
        int $authStatus,
        string $unpUsed,
        string $unpFromAuthServer
    ): string {
        if ($authStatus !== 3 || $authzStatus !== 'authzSuccess') {
            return $authzStatus;
        }

        if (
            $unpFromAuthServer !== ''
            && ($unpUsed === '' || strcasecmp($unpFromAuthServer, $unpUsed) !== 0)
        ) {
            return 'authzFail';
        }

        return $authzStatus;
    }

    private function adjustAosAuthzStatusForServerMessage(
        string $authzStatus,
        string $serverMessage
    ): string {
        if ($serverMessage === '' || $serverMessage === '-') {
            return $authzStatus;
        }

        $message = strtolower($serverMessage);

        if (
            str_contains($message, 'block')
            || str_contains($message, 'deny')
            || str_contains($message, 'reject')
            || str_contains($message, 'unauthor')
            || str_contains($message, 'quarantine')
            || str_contains($message, 'critical')
        ) {
            return 'authzFail';
        }

        return $authzStatus;
    }

    private function normalizeAosDaString(string $value): string
    {
        $value = trim($value);
        $value = trim($value, "\"'");

        return ($value === '' || $value === '-') ? '' : $value;
    }

    private function parseAosDaIndex(string $index): array
    {
        $parts = explode('.', $index);

        if (count($parts) === 3) {
            $ifIndex = (int) $parts[0];
            $macRaw = (string) $parts[1];
            $vlanRaw = $parts[2];

            if ($ifIndex <= 0 || ! is_numeric($vlanRaw)) {
                return [0, '', '', null];
            }

            $macColon = $this->normalizeMacColon($macRaw);

            return [
                $ifIndex,
                $macColon,
                $this->normalizeMacNoSep($macColon),
                (int) $vlanRaw,
            ];
        }

        if (count($parts) >= 8) {
            $ifIndex = (int) $parts[0];
            $macBytes = array_slice($parts, 1, 6);
            $vlan = $parts[7];

            if ($ifIndex <= 0 || ! is_numeric($vlan)) {
                return [0, '', '', null];
            }

            $macColon = strtolower(implode(':', array_map(
                static fn ($byte) => str_pad(
                    dechex((int) $byte),
                    2,
                    '0',
                    STR_PAD_LEFT
                ),
                $macBytes
            )));

            return [
                $ifIndex,
                $macColon,
                $this->normalizeMacNoSep($macColon),
                (int) $vlan,
            ];
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

        return implode(':', $this->normalizeMacIndexGroups($parts));
    }

    private function normalizeMacIndexGroups(array $parts): array
    {
        return array_map(static function ($part) {
            $part = strtolower(trim((string) $part));
            $part = preg_replace('/[^0-9a-f]/', '', $part) ?? '';

            return str_pad($part, 2, '0', STR_PAD_LEFT);
        }, $parts);
    }

    private function normalizeMacNoSep(string $mac): string
    {
        return strtolower(str_replace(':', '', trim($mac)));
    }

    private function rowValue($row, string $oid)
    {
        if (is_array($row) && array_key_exists($oid, $row)) {
            return $row[$oid];
        }

        $shortOid = preg_replace('/^.*::/', '', $oid);

        if (is_array($row) && array_key_exists($shortOid, $row)) {
            return $row[$shortOid];
        }

        return null;
    }

    private function parseAosTimestamp(string $timestamp): int
    {
        $timestamp = trim($timestamp);

        if (! preg_match('/(\d+)-(\d+)-(\d+),(\d+):(\d+):(\d+)/', $timestamp, $matches)) {
            return 0;
        }

        $date = sprintf(
            '%04d-%02d-%02d %02d:%02d:%02d',
            $matches[1],
            $matches[2],
            $matches[3],
            $matches[4],
            $matches[5],
            $matches[6]
        );

        $startTime = strtotime($date);

        return $startTime ? time() - $startTime : 0;
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
                    'port_id' => PortCache::getIdFromIfIndex(
                        $vpaIfIndex,
                        $this->getDeviceId()
                    ) ?? 0,
                ]);
            })->filter();
    }

    public function discoverTransceivers(): Collection
    {
        $device = $this->getDevice();
        $transceivers = collect();
        $ports = $device->ports()->get()->keyBy('ifIndex');
        $invalidValue = -2147483648;

        $ddmData = SnmpQuery::mibDir('nokia/aos7')
            ->walk('ALCATEL-IND1-PORT-MIB::ddmPortRxOpticalPower')
            ->valuesByIndex();

        $entities = $device->entityPhysical()
            ->where('entPhysicalName', 'like', '%TRANSCEIVER%')
            ->get();

        foreach ($ddmData as $index => $entry) {
            $value = is_array($entry)
                ? ($entry['ALCATEL-IND1-PORT-MIB::ddmPortRxOpticalPower'] ?? $invalidValue)
                : $entry;

            if ($value <= $invalidValue) {
                continue;
            }

            $parts = explode('.', (string) $index);
            $ifIndex = (int) $parts[0];

            if ($ifIndex <= 0) {
                continue;
            }

            $port = $ports->get($ifIndex);

            if (! $port) {
                continue;
            }

            $entity = null;

            if (
                $port->ifName
                && preg_match('/^(\d+)\/(\d+)\/(\d+)[A-Z]?$/', $port->ifName, $matches)
            ) {
                $entityName = "{$matches[1]}/SLOT-{$matches[2]} TRANSCEIVER-{$matches[3]}";

                $entity = $entities->first(
                    fn ($item) => $item->entPhysicalName === $entityName
                );
            }

            $transceivers->push(new Transceiver([
                'port_id' => $port->port_id,
                'index' => $ifIndex,
                'entity_physical_index' => $ifIndex,
                'type' => $entity ? ($entity->entPhysicalDescr ?: null) : 'SFP',
                'vendor' => $entity?->entPhysicalMfgName ?: null,
                'model' => $entity?->entPhysicalModelName ?: null,
                'serial' => $entity?->entPhysicalSerialNum ?: null,
                'revision' => $entity?->entPhysicalHardwareRev ?: null,
                'ddm' => 1,
            ]));
        }

        return $transceivers;
    }
}
