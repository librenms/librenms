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
use App\Models\PortsNac;
use App\Models\PortVlan;
use App\Models\Transceiver;
use App\Models\Vlan;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use LibreNMS\Discovery\Neighbors\NeighborParser;
use LibreNMS\Interfaces\Discovery\BgpAddressFamilyDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Discovery\VlanDiscovery;
use LibreNMS\Interfaces\Discovery\VlanPortDiscovery;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\OS;
use LibreNMS\OS\Traits\AlcatelMacAddressMib;
use LibreNMS\Util\Bgp;
use LibreNMS\Util\IP;
use LibreNMS\Util\Mac;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

class Aos7 extends OS implements BgpAddressFamilyDiscovery, ProcessorDiscovery, VlanDiscovery, VlanPortDiscovery, TransceiverDiscovery, NacPolling
{
    use AlcatelMacAddressMib;

    private const int NAC_AUTH_IDLE = 1;
    private const int NAC_AUTHENTICATED = 3;
    private const int NAC_NO_MATCHING_UNP_BLOCK = 60;

    public function discoverProcessors(): Collection
    {
        $processors = collect();
        $cmm = null;
        foreach ($this->discoverYamlProcessors() as $processor) {
            $chassis = (int) substr($processor->processor_index, 3);
            if ($chassis === 0 && str_ends_with($processor->processor_oid, '.0')) {
                $cmm = $processor->fill(['processor_index' => '0', 'processor_descr' => 'Processor']);
            }

            // The first numbered health row for each chassis represents that VC member.
            if ($chassis > 0 && str_ends_with($processor->processor_oid, '.' . (($chassis - 1) * 1000 + 1))) {
                $processors->push($processor);
            }
        }

        return $processors->isEmpty() && $cmm !== null ? collect([$cmm]) : $processors;
    }

    /** @return Collection<int, PortsNac> */
    public function pollNac(): Collection
    {
        $nac = collect();

        $rows = SnmpQuery::numericIndex()->walk('ALCATEL-IND1-DA-MIB::alaDaMacVlanUserTable')
            ->throwIfConnectionError()
            ->valuesByIndex();

        if (empty($rows)) {
            return $nac;
        }

        // Read address octets in hex so printable bytes cannot be mistaken for text.
        $ipOid = 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserIpAddress';
        $ipAddresses = SnmpQuery::options('-OQUSXx')
            ->numericIndex()
            ->walk($ipOid)
            ->throwIfConnectionError()
            ->pluck($ipOid);

        $portTimeoutByIfIndex = null;
        $prefix = 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUser';

        $authenticatedMacs = [];
        foreach ($rows as $index => $row) {
            $client = $this->parseNacIndex((string) $index);
            if ($client === null) {
                continue;
            }

            [$ifIndex, $mac, $vlan] = $client;
            $macAddress = $mac->hex();
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

            if ($authStatus === self::NAC_AUTHENTICATED) {
                $authenticatedMacs[$portId][$macAddress] = true;
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
                $ipAddresses[$index] ?? '',
                (string) ($row[$prefix . 'IpAddress'] ?? '')
            );

            $nac->push(new PortsNac([
                'port_id' => $portId,
                'auth_id' => "$ifIndex-$macAddress-$vlan",
                'domain' => $domain,
                'username' => (string) ($row[$prefix . 'Name'] ?? $mac->readable()),
                'mac_address' => $macAddress,
                'ip_address' => $ip,
                'authz_status' => $isNoMatchingUnpBlock || $unpMismatch ? 'authzFail' : 'authz' . $status,
                'authz_by' => $authzBy,
                'authc_status' => 'authc' . $status,
                'method' => $method,
                'timeout' => $timeout,
                'time_left' => null,
                'vlan' => $vlan,
                'time_elapsed' => $this->nacTimeElapsed($row[$prefix . 'LoginTimeStamp'] ?? ''),
            ]));
        }

        foreach ($nac as $entry) {
            $entry->host_mode = count($authenticatedMacs[$entry->port_id] ?? []) > 1 ? 'multiAuth' : 'singleHost';
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
        $hex = preg_replace('/[\s:.-]+/', '', preg_replace('/^0x/i', '', trim($hex, " \"'")));

        return strlen($hex) === 8 && StringHelpers::isHex($hex) ? IP::fromHexString($hex, true)?->uncompressed() : null;
    }

    private function normalizeAosDaString(string $value): string
    {
        $value = trim(trim($value), "\"'");

        return $value === '-' ? '' : $value;
    }

    /**
     * @return array{int, Mac, int}|null
     */
    private function parseNacIndex(string $index): ?array
    {
        $parts = explode('.', $index);
        if (count($parts) !== 8 || ! ctype_digit($parts[0]) || (int) $parts[0] < 1 || ! ctype_digit($parts[7])) {
            return null;
        }

        // Numeric indexes contain the ifIndex, six MAC octets, and the VLAN.
        $octets = array_slice($parts, 1, 6);
        if (! array_all($octets, fn (string $octet) => ctype_digit($octet) && (int) $octet <= 255)) {
            return null;
        }

        return [(int) $parts[0], Mac::parse(bin2hex(pack('C*', ...$octets))), (int) $parts[7]];
    }

    private function nacTimeElapsed(string $timestamp): ?int
    {
        $timestamp = $this->normalizeAosDaString($timestamp);
        if (trim($timestamp) === '') {
            return null;
        }

        try {
            $start = Date::parse($timestamp)->getTimestamp();
            $elapsed = Date::now()->getTimestamp() - $start;

            return $start && $elapsed > 0 ? $elapsed : null;
        } catch (InvalidFormatException) {
            return null;
        }
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
                    'port_id' => PortCache::getIdFromIfIndex($vpaIfIndex, $this->getDeviceId()) ?? 0,
                ]);
            })->filter();
    }

    /** @return Collection<int, Transceiver> */
    public function discoverTransceivers(): Collection
    {
        $voltageOid = 'ALCATEL-IND1-PORT-MIB::ddmPortSupplyVoltage';
        $voltages = SnmpQuery::walk($voltageOid)
            ->throwIfConnectionError()
            ->pluck($voltageOid);

        // The DDM table is indexed by ifIndex and channel.
        $channelsByPort = [];
        foreach ($voltages as $index => $voltage) {
            if (! is_numeric($voltage) || $voltage <= 0) {
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

        foreach ($ports as $ifIndex => $port) {
            $entity = null;

            if (preg_match('/^(\d+)\/(\d+)\/(\d+)[A-Z]?$/', $port->ifName ?? '', $matches)) {
                [, $chassis, $slot, $portNumber] = $matches;
                $entityName = "$chassis/SLOT-$slot TRANSCEIVER-$portNumber";
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
                'channels' => $channelsByPort[$ifIndex],
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
        $descr = NeighborParser::parseText($this->lldpLocPortTable()[$lldpPortNum]['lldpLocPortDesc'] ?? '');

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
