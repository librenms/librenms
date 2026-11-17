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
use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use App\Models\Mempool;
use App\Models\PortsNac;
use App\Models\PortVlan;
use App\Models\Processor;
use App\Models\Transceiver;
use App\Models\Vlan;
use Illuminate\Support\Collection;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Enum\SnmpStringOutput;
use LibreNMS\Discovery\Neighbors\NeighborParser;
use LibreNMS\Interfaces\Discovery\BgpAddressFamilyDiscovery;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Discovery\VlanDiscovery;
use LibreNMS\Interfaces\Discovery\VlanPortDiscovery;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\OS;
use LibreNMS\OS\Traits\AlcatelMacAddressMib;
use LibreNMS\Util\Bgp;
use LibreNMS\Util\Mac;
use SnmpQuery;

class Aos7 extends OS implements BgpAddressFamilyDiscovery, MempoolsDiscovery, ProcessorDiscovery, VlanDiscovery, VlanPortDiscovery, TransceiverDiscovery, NacPolling
{
    use AlcatelMacAddressMib;

    private const int NAC_AUTH_IDLE = 1;
    private const int NAC_AUTHENTICATED = 3;
    private const int NAC_NO_MATCHING_UNP_BLOCK = 60;

    /** @var array<int|string, string>|null */
    private ?array $healthModuleChassisIds = null;

    /**
     * @param  array<int|string, array<string, int|string>>  $rows
     * @return array<int|string, array<string, int|string>>
     */
    public function ddmRowsWithPortNames(array $rows): array
    {
        $names = SnmpQuery::cache()->walk('IF-MIB::ifName')->pluck();
        $interfaceNames = array_fill_keys($names, true);

        foreach (array_keys($rows) as $index) {
            [$ifIndex, $channel] = explode('.', (string) $index, 2);
            $name = $names[$ifIndex] ?? $ifIndex;
            $sensorName = $channel > 1 ? "$name Lane $channel" : $name;

            // The table uses the cage's base ifIndex, even for breakout lanes.
            // Use the OID index: some firmware returns inconsistent ddmPortChannel values.
            if (preg_match('/^(\d+\/\d+\/\d+)A$/', $name, $matches) && $channel >= 1 && $channel <= 4) {
                $laneName = $matches[1] . chr(64 + (int) $channel);
                if (isset($interfaceNames[$laneName])) {
                    $sensorName = $laneName;
                }
            }

            $rows[$index]['aos7DdmPortName'] = $sensorName;
        }

        return $rows;
    }

    public function discoverProcessors(): Collection
    {
        $usage = SnmpQuery::walk('ALCATEL-IND1-HEALTH-MIB::healthModuleCpu1MinAvg')
            ->throwIfConnectionError()
            ->pluck();
        $chassisIds = $this->chassisIdsBySlot();
        $readings = $this->primaryChassisReadings($usage, $chassisIds);

        if ($readings === []) {
            // Chassis ID 0 indicates that no VC ID is configured. Keep the original CMM CPU.
            $chassisId = $chassisIds[0] ?? null;
            $percent = $usage[0] ?? null;
            if (is_numeric($chassisId) && (float) $chassisId === 0.0 && is_numeric($percent) && $percent >= 0 && $percent <= 100) {
                $readings[0] = [0, $percent];
            }
        }

        $processors = new Collection;
        foreach ($readings as $chassisId => [$slot, $percent]) {
            $processors->push(new Processor([
                'processor_type' => 'aos7',
                'processor_oid' => '.1.3.6.1.4.1.6486.801.1.2.1.16.1.1.1.1.1.11.' . $slot,
                'processor_index' => $chassisId === 0 ? '0' : "vc.$chassisId",
                'processor_descr' => $chassisId === 0 ? 'Processor' : "VC Unit $chassisId CPU",
                'processor_precision' => 1,
                'processor_usage' => $percent,
            ]));
        }

        return $processors;
    }

    public function discoverMempools(): Collection
    {
        // RAM capacity in bytes, indexed by virtual-chassis unit.
        $capacities = SnmpQuery::walk('ALCATEL-IND1-SYSTEM-MIB::systemVcHardwareMemorySize')
            ->throwIfConnectionError()
            ->pluck();

        if (empty($capacities)) {
            return $this->discoverYamlMempools();
        }

        $usage = SnmpQuery::walk('ALCATEL-IND1-HEALTH-MIB::healthModuleMemory1MinAvg')
            ->throwIfConnectionError()
            ->pluck();
        $readings = $this->primaryChassisReadings($usage, $this->chassisIdsBySlot());
        $mempools = collect();

        foreach ($capacities as $chassisId => $total) {
            if (! isset($readings[$chassisId]) || ! is_numeric($total) || $total <= 0) {
                continue;
            }

            [$slot, $percent] = $readings[$chassisId];
            $mempool = new Mempool([
                'mempool_type' => 'aos7',
                'mempool_class' => 'module',
                'mempool_index' => "vc.$chassisId",
                'mempool_descr' => "VC Unit $chassisId Memory",
                'mempool_precision' => 1,
                'mempool_total_oid' => '.1.3.6.1.4.1.6486.801.1.1.1.2.1.1.10.1.1.6.' . $chassisId,
                'mempool_perc_oid' => '.1.3.6.1.4.1.6486.801.1.2.1.16.1.1.1.1.1.8.' . $slot,
            ]);

            $mempool->fillUsage(
                total: (float) $total,
                percent: $percent
            );

            if ($mempool->isValid()) {
                $mempools->push($mempool);
            }
        }

        return $mempools->isNotEmpty()
            ? $mempools
            : $this->discoverYamlMempools();
    }

    /**
     * @return array<int|string, string>
     */
    private function chassisIdsBySlot(): array
    {
        return $this->healthModuleChassisIds ??= SnmpQuery::walk('ALCATEL-IND1-HEALTH-MIB::healthModuleChassisId')
            ->throwIfConnectionError()
            ->pluck();
    }

    /**
     * @param  array<int|string, string>  $usage
     * @param  array<int|string, string>  $chassisIds
     * @return array<int, array{int|string, float}>
     */
    private function primaryChassisReadings(array $usage, array $chassisIds): array
    {
        $readings = [];

        foreach ($usage as $slot => $percent) {
            $chassisId = (int) ($chassisIds[$slot] ?? 0);
            // Row 0 represents the CMM. The first numbered row for each unit represents that VC member.
            if ($chassisId > 0 && (int) $slot === ($chassisId - 1) * 1000 + 1
                && is_numeric($percent) && $percent >= 0 && $percent <= 100
            ) {
                $readings[$chassisId] = [$slot, (float) $percent];
            }
        }

        return $readings;
    }

    public function pollNac(): Collection
    {
        $nac = collect();

        $rows = SnmpQuery::walk('ALCATEL-IND1-DA-MIB::alaDaMacVlanUserTable')
            ->throwIfConnectionError()
            ->valuesByIndex();

        if (empty($rows)) {
            return $nac;
        }

        // Read address octets in hex so printable bytes cannot be mistaken for text.
        $ipOptions = SnmpQueryOptions::quickPrint();
        $ipOptions->stringFormat = SnmpStringOutput::Hex;
        $ipRows = SnmpQuery::options($ipOptions)
            ->walk('ALCATEL-IND1-DA-MIB::alaDaMacVlanUserIpAddress')
            ->throwIfConnectionError()
            ->valuesByIndex();

        $portTimeoutByIfIndex = null;
        $prefix = 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUser';

        $clients = [];
        $authenticatedCountByIfIndex = [];

        foreach ($rows as $index => $row) {
            $client = $this->parseNacIndex((string) $index);
            if ($client === null) {
                continue;
            }

            $clients[$index] = $client;
            if ((int) ($row[$prefix . 'AuthStatus'] ?? 0) === self::NAC_AUTHENTICATED) {
                [$ifIndex] = $client;
                $authenticatedCountByIfIndex[$ifIndex] =
                    ($authenticatedCountByIfIndex[$ifIndex] ?? 0) + 1;
            }
        }

        foreach ($clients as $index => [$ifIndex, $mac, $vlan]) {
            $row = $rows[$index];
            $authStatus = (int) ($row[$prefix . 'AuthStatus'] ?? 0);
            $classificationSource = (int) ($row[$prefix . 'ClassificationSource'] ?? 0);
            $isNoMatchingUnpBlock = $classificationSource === self::NAC_NO_MATCHING_UNP_BLOCK;

            // AOS normally reports inactive users as idle(1), which we do not
            // need to retain. However, "No Matching UNP - Block" is also
            // reported as idle(1), so keep that entry visible in LibreNMS.
            if ($authStatus === self::NAC_AUTH_IDLE && ! $isNoMatchingUnpBlock) {
                continue;
            }

            $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId());

            if (! $portId) {
                continue;
            }

            $authType = (int) ($row[$prefix . 'Authtype'] ?? 0);
            $unpUsed = $this->normalizeAosDaString((string) ($row[$prefix . 'UnpUsed'] ?? ''));
            $unpFromAuthServer = $this->normalizeAosDaString((string) ($row[$prefix . 'UnpFromAuthServer'] ?? ''));
            $authServer = (string) ($row[$prefix . 'AuthServerUsed'] ?? '');

            $method = match ($authType) {
                0 => 'mab',
                2 => 'dot1x',
                default => 'unknown',
            };

            $status = match ($authStatus) {
                self::NAC_AUTH_IDLE => 'Idle',
                2 => 'InProgress',
                self::NAC_AUTHENTICATED => 'Success',
                4, 5, 6, 7 => 'Fail',
                default => 'Unknown',
            };
            // Authentication can succeed even if the requested UNP was not applied.
            $unpMismatch = $authStatus === self::NAC_AUTHENTICATED && $unpFromAuthServer !== ''
                && strcasecmp($unpFromAuthServer, $unpUsed) !== 0;

            $timeout = 0;
            if ($method === 'dot1x') {
                $portTimeoutByIfIndex ??= SnmpQuery::walk('ALCATEL-IND1-DA-MIB::alaDaUNPPort8021XSuppTimeOut')->pluck();
                $portTimeout = $portTimeoutByIfIndex[$ifIndex] ?? null;
                $timeout = is_numeric($portTimeout) ? (int) $portTimeout : 0;
            }

            $authzBy = match (true) {
                $isNoMatchingUnpBlock => 'UNP',
                $authServer === '', $authServer === '-' => 'RADIUS',
                default => $authServer,
            };

            $domain = match (true) {
                $isNoMatchingUnpBlock => 'No Matching UNP - Block',
                $unpUsed !== '' => $unpUsed,
                $unpFromAuthServer !== '' => $unpFromAuthServer,
                default => 'UNP',
            };

            $ip = $this->parseNacIp(
                (string) ($ipRows[$index][$prefix . 'IpAddress'] ?? ''),
                (string) ($row[$prefix . 'IpAddress'] ?? '')
            );
            $timeElapsed = $this->parseAosTimestamp((string) ($row[$prefix . 'LoginTimeStamp'] ?? ''));

            $nac->push(new PortsNac([
                'port_id' => $portId,
                'auth_id' => "$ifIndex-{$mac->hex()}-$vlan",
                'domain' => $domain,
                'username' => (string) ($row[$prefix . 'Name'] ?? $mac->readable()),
                'mac_address' => $mac->hex(),
                'ip_address' => $ip,
                'host_mode' => ($authenticatedCountByIfIndex[$ifIndex] ?? 0) > 1 ? 'multiAuth' : 'singleHost',
                'authz_status' => $isNoMatchingUnpBlock || $unpMismatch ? 'authzFail' : 'authz' . $status,
                'authz_by' => $authzBy,
                'authc_status' => 'authc' . $status,
                'method' => $method,
                'timeout' => $timeout,
                'time_left' => null,
                'vlan' => $vlan,
                'time_elapsed' => $timeElapsed > 0 ? $timeElapsed : null,
            ]));
        }

        return $nac;
    }

    private function parseNacIp(string $hex, string $raw): string
    {
        $ip = $this->decodeHexIp($hex);
        if ($ip !== null) {
            return $ip;
        }

        $raw = trim($raw, " \"'");
        if (filter_var($raw, FILTER_VALIDATE_IP)) {
            return $raw;
        }

        // Prefer a decimal IPv4 integer over hex when the fallback is all digits.
        if (ctype_digit($raw) && strlen($raw) <= 10 && (float) $raw <= 4294967295) {
            return long2ip((int) $raw) ?: '0.0.0.0';
        }

        return $this->decodeHexIp($raw) ?? '0.0.0.0';
    }

    private function decodeHexIp(string $hex): ?string
    {
        $hex = trim($hex, " \"'");

        if (str_starts_with(strtolower($hex), '0x')) {
            $hex = substr($hex, 2);
        }

        // AOS7 currently reports IPv4 here. Accept exactly four octets,
        // either contiguous (C0A80101) or separated (C0 A8 01 01).
        if (preg_match('/^[0-9a-fA-F]{8}$/D', $hex)) {
            $bytes = hex2bin($hex);
        } elseif (preg_match('/^[0-9a-fA-F]{2}(?:[\s:.-]+[0-9a-fA-F]{2}){3}$/D', $hex)) {
            $bytes = hex2bin(preg_replace('/[\s:.-]+/', '', $hex));
        } else {
            return null;
        }

        return $bytes === false ? null : (inet_ntop($bytes) ?: null);
    }

    private function normalizeAosDaString(string $value): string
    {
        $value = trim($value);
        $value = trim($value, "\"'");

        return ($value === '' || $value === '-') ? '' : $value;
    }

    /**
     * @return array{int, Mac, int}|null
     */
    private function parseNacIndex(string $index): ?array
    {
        $parts = explode('.', $index);

        if (count($parts) === 3) {
            [$ifIndex, $address, $vlan] = $parts;
        } elseif (count($parts) === 8) {
            // Net-SNMP can render the MAC index as six decimal octets.
            $ifIndex = $parts[0];
            $vlan = $parts[7];
            $octets = array_slice($parts, 1, 6);
            foreach ($octets as $octet) {
                if (! ctype_digit($octet) || (int) $octet > 255) {
                    return null;
                }
            }
            $address = implode(':', array_map(static fn (string $octet) => sprintf('%02x', (int) $octet), $octets));
        } else {
            return null;
        }

        $mac = Mac::parse($address);

        return ctype_digit($ifIndex) && (int) $ifIndex > 0 && ctype_digit($vlan) && $mac->isValid()
            ? [(int) $ifIndex, $mac, (int) $vlan]
            : null;
    }

    private function parseAosTimestamp(string $timestamp): int
    {
        $timestamp = trim($timestamp);

        // DateAndTime: 2026-10-13,9:0:35.0,+2:0 (the UTC offset is optional)
        if (! preg_match('/(\d+)-(\d+)-(\d+),(\d+):(\d+):(\d+)(?:\.\d+)?(?:,([+-])(\d+):(\d+))?/', $timestamp, $matches)) {
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

        if (isset($matches[7])) {
            // use the offset the device reports, not the timezone of the server
            $date .= sprintf(' %s%02d:%02d', $matches[7], $matches[8], $matches[9]);
        }

        $startTime = strtotime($date);

        return $startTime ? time() - $startTime : 0;
    }

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
        $ddmData = SnmpQuery::walk('ALCATEL-IND1-PORT-MIB::ddmPortRxOpticalPower')
            ->throwIfConnectionError()
            ->pluck();

        // The DDM table is indexed by ifIndex and channel.
        $channelsByPort = [];
        foreach ($ddmData as $index => $value) {
            if (! is_numeric($value) || $value <= -2147483648 || (int) $value === -200000) {
                continue;
            }

            [$ifIndex] = explode('.', (string) $index, 2);
            $ifIndex = (int) $ifIndex;
            if ($ifIndex > 0) {
                $channelsByPort[$ifIndex] = ($channelsByPort[$ifIndex] ?? 0) + 1;
            }
        }

        if (empty($channelsByPort)) {
            return collect();
        }

        $device = $this->getDevice();
        $ports = $device->ports()
            ->whereIn('ifIndex', array_keys($channelsByPort))
            ->get(['port_id', 'ifIndex', 'ifName'])
            ->keyBy('ifIndex');
        $entities = $device->entityPhysical()
            ->where('entPhysicalName', 'like', '%TRANSCEIVER%')
            ->get()
            ->uniqueStrict('entPhysicalName')
            ->keyBy('entPhysicalName');
        $transceivers = collect();

        foreach ($channelsByPort as $ifIndex => $channels) {
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

                $entity = $entities->get($entityName);
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
                'channels' => $channels,
            ]));
        }

        return $transceivers;
    }

    /**
     * AOS7 numbers LLDP ports with its own scheme,
     * map them by description instead.
     */
    protected function lldpLocalPortId(int $lldpPortNum): ?int
    {
        $descr = NeighborParser::parseText(
            $this->lldpLocPortTable()[$lldpPortNum]['lldpLocPortDesc'] ?? ''
        );

        return $this->lldpLocalPorts()->byDescr($descr)?->port_id;
    }

    /**
     * Sessions are read from BGP4-MIB, ALCATEL-IND1-BGP-MIB adds their name
     *
     * @return Collection<int, BgpPeer>
     */
    public function discoverBgpPeers(): Collection
    {
        $peers = new Collection;

        foreach ($this->getDevice()->getVrfContexts() as $context) {
            $names = [];
            foreach ($this->walkBgpTable($context, 'ALCATEL-IND1-BGP-MIB', ['bgpPeerDescr' => 'alaBgpPeerName']) as $index => $row) {
                $parts = explode('.', (string) $index);
                $names[(string) Bgp::shiftIp($parts)?->compressed()] = $row['bgpPeerDescr'];
            }

            $peers = $peers->merge($this->discoverBgp4MibPeers($context)->each(function (BgpPeer $peer) use ($names): void {
                $peer->bgpPeerDescr = $names[$peer->bgpPeerIdentifier] ?? $peer->bgpPeerDescr;
            }));
        }

        return $peers->values();
    }

    /**
     * Only received IPv4 unicast prefixes are reported
     *
     * @return Collection<int, BgpPeerCbgp>
     */
    public function discoverBgpAddressFamilies(Collection $peers): Collection
    {
        $families = new Collection;

        foreach ($peers->pluck('context_name')->unique() as $context) {
            foreach ($this->walkBgpTable($context, 'ALCATEL-IND1-BGP-MIB', ['AcceptedPrefixes' => 'alaBgpPeerRcvdPrefixes']) as $index => $counters) {
                $parts = explode('.', (string) $index);
                $families->push($this->makeBgpAddressFamily(Bgp::shiftIp($parts), null, $context, 'ipv4', 'unicast', $counters));
            }
        }

        return $families->filter()->values();
    }

    public function discoverFdbTable(): Collection
    {
        $fdb = SnmpQuery::walk('ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressGblManagement')
            ->mapTable(fn ($data, $domain, $locale, $bridgePort, $vlan, $subId, $mac) => $subId == 0
                ? $this->alcatelFdbEntry($vlan, $mac, (int) $bridgePort, $this->bridgePortIfIndexes())
                : null)
            ->filter();

        if ($fdb->isEmpty()) {
            $fdb = $this->alcatelDispositionFdb();
        }

        return $fdb->isNotEmpty() ? $fdb : parent::discoverFdbTable();
    }
}
