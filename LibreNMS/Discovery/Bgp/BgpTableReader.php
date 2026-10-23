<?php

/**
 * BgpTableReader.php
 *
 * Reads BGP sessions from the snmp tables described in os discovery yaml.
 * BGP4-MIB cannot describe IPv6 sessions and BGP4-V2-MIB never left draft,
 * so most vendors ship their own copy of one of them. Each table only needs
 * a field to column map and its index layout, BGP4-MIB is read when the os
 * tables report no sessions.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Discovery\Bgp;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Util\IP;
use LibreNMS\Util\Number;
use SnmpQuery;

/**
 * A table maps field names to oids (or literal values) and describes its index.
 * Rows from every table are merged by VRF and address, the first table to set a field wins.
 * Tables marked fallback are only read when the others found nothing.
 *
 * @phpstan-type Table array<string, mixed>
 * @phpstan-type Row array<string, mixed>
 */
class BgpTableReader
{
    private const BGP4_MIB = [
        'index_layout' => ['ip'],
        'fallback' => true,
        'remote_as' => 'BGP4-MIB::bgpPeerRemoteAs',
        'state' => 'BGP4-MIB::bgpPeerState',
        'admin_status' => 'BGP4-MIB::bgpPeerAdminStatus',
        'local_address' => 'BGP4-MIB::bgpPeerLocalAddr',
        'last_error' => 'BGP4-MIB::bgpPeerLastError',
        'in_updates' => 'BGP4-MIB::bgpPeerInUpdates',
        'out_updates' => 'BGP4-MIB::bgpPeerOutUpdates',
        'in_messages' => 'BGP4-MIB::bgpPeerInTotalMessages',
        'out_messages' => 'BGP4-MIB::bgpPeerOutTotalMessages',
        'established_time' => 'BGP4-MIB::bgpPeerFsmEstablishedTime',
        'update_elapsed_time' => 'BGP4-MIB::bgpPeerInUpdateElapsedTime',
    ];

    /** AS_TRANS, reported in place of a 4-byte AS by 2-byte only objects */
    private const AS_TRANS = 23456;

    /** InetAddressType to [index length, address length], the z types carry a 4 octet zone */
    private const ADDRESS_LENGTHS = [1 => [4, 4], 2 => [16, 16], 3 => [8, 4], 4 => [20, 16]];

    private const ADDRESS_TOKENS = ['address', 'skip_address', 'ip'];

    private const AFI = [1 => 'ipv4', 2 => 'ipv6', 25 => 'l2vpn'];

    private const SAFI = [
        1 => 'unicast',
        2 => 'multicast',
        3 => 'unicastAndMulticast',
        4 => 'labeledUnicast',
        5 => 'mvpn',
        65 => 'vpls',
        70 => 'evpn',
        128 => 'vpn',
        129 => 'vpnMulticast',
        132 => 'rtfilter',
        133 => 'flow',
    ];

    private const STATES = ['idle', 'connect', 'active', 'opensent', 'openconfirm', 'established'];

    private const INTEGERS = ['remote_as', 'local_as', 'last_error_code', 'last_error_subcode', 'established_time', 'update_elapsed_time'];

    private const COUNTERS = [
        'in_updates',
        'out_updates',
        'in_messages',
        'out_messages',
        'accepted',
        'denied',
        'admin_limit',
        'threshold',
        'clear_threshold',
        'advertised',
        'suppressed',
        'withdrawn',
    ];

    /** @var Table[] */
    private readonly array $peerTables;
    /** @var Table[] */
    private readonly array $familyTables;
    private readonly ?string $localAsOid;

    /**
     * @param  array{peers?: Table[], address_families?: Table[], local_as?: string}  $definition  bgp-peers module of os discovery yaml
     */
    public function __construct(array $definition = [])
    {
        $this->peerTables = [...($definition['peers'] ?? []), self::BGP4_MIB];
        $this->familyTables = $definition['address_families'] ?? [];
        $this->localAsOid = $definition['local_as'] ?? null;
    }

    /**
     * Link-local addresses are only unique on their link, so those sessions (and unnumbered sessions
     * that have no address yet) are identified by their interface instead.
     *
     * @param  Row  $row
     */
    public static function identifiedByInterface(array $row): bool
    {
        return isset($row['iface']) && (! ($row['address'] ?? null) instanceof IP || $row['address']->isLinkLocal());
    }

    /**
     * Tables that identify the VRF report every VRF from the default context,
     * otherwise each SNMP context must be read.
     */
    public function reportsAllVrfs(): bool
    {
        foreach ($this->peerTables as $table) {
            if (in_array('vrf', $table['index_layout'] ?? []) || isset($table['vrf']) || isset($table['vrf_name'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sessions and their address families in the given context.
     * Sessions are only kept if some table reported their remote AS.
     *
     * @return array{peers: Row[], address_families: Row[]}
     */
    public function read(string $context): array
    {
        $peers = array_filter(
            $this->readTables($context, $this->peerTables, 'remote_as', fn (array $row) => ($row['vrf'] ?? $row['vrf_name'] ?? '') . '|' . $this->peerKey($row)),
            fn (array $row) => isset($row['remote_as']),
        );

        $peerAddresses = array_column(array_filter($peers, fn (array $row) => isset($row['peer_index'])), 'address', 'peer_index');
        $families = $this->readTables($context, $this->familyTables, 'afi', fn (array $row) => isset($row['afi'], $row['safi']) ? $this->peerKey($row) . "|{$row['afi']}|{$row['safi']}" : null, $peerAddresses);

        return ['peers' => array_values($peers), 'address_families' => array_values($families)];
    }

    /**
     * BGP4-MIB only holds a 2-byte AS, use the local AS reported for the sessions when it doesn't fit.
     *
     * @param  Row[]  $peers
     */
    public function localAs(array $peers): ?int
    {
        $as = SnmpQuery::get('BGP4-MIB::bgpLocalAs.0')->value();

        if (empty($as) || $as == self::AS_TRANS) {
            $as = Arr::first(array_column($peers, 'local_as'), fn ($localAs) => $localAs > 0) ?? $as;
        }

        if ((empty($as) || $as == self::AS_TRANS) && $this->localAsOid) {
            $as = Arr::first(SnmpQuery::walk($this->localAsOid)->values(), fn ($localAs) => $localAs > 0) ?? $as;
        }

        return is_numeric($as) ? Number::correctIntegerOverflow((int) $as) ?: null : null;
    }

    /**
     * Walk a table one column at a time and group the values by numeric index.
     * The first oid is walked alone so devices without the table only cost a single walk.
     *
     * @param  string[]  $oids
     * @return array<string, array<string, string>>
     */
    protected function walk(string $context, array $oids): array
    {
        $first = SnmpQuery::context($context)->hideMib()->enumStrings()->numericIndex()->walk(array_shift($oids));
        if (! $first->isValid()) {
            return [];
        }

        $rows = $first->valuesByIndex();
        if (! empty($oids)) {
            SnmpQuery::context($context)->hideMib()->enumStrings()->numericIndex()->walk($oids)->valuesByIndex($rows);
        }

        return $rows;
    }

    /**
     * @param  Row  $row
     */
    private function peerKey(array $row): string
    {
        return self::identifiedByInterface($row) ? "iface:{$row['iface']}" : (string) $row['address'];
    }

    /**
     * Read and merge tables, fallback tables are only read if no other table reported the required field
     *
     * @param  Table[]  $tables
     * @param  callable(Row): ?string  $key
     * @param  array<int|string, IP>  $peerAddresses  address by peer_index, for tables indexed by it
     * @return array<string, Row>
     */
    private function readTables(string $context, array $tables, string $required, callable $key, array $peerAddresses = []): array
    {
        $rows = [];

        foreach ([false, true] as $fallback) {
            foreach ($tables as $table) {
                if (($table['fallback'] ?? false) !== $fallback) {
                    continue;
                }

                foreach ($this->readTable($context, $table, $peerAddresses) as $row) {
                    $rowKey = $key($row);
                    if ($rowKey !== null) {
                        $rows[$rowKey] = ($rows[$rowKey] ?? []) + $row;
                    }
                }
            }

            if (! empty(array_column($rows, $required))) {
                break;
            }
        }

        return $rows;
    }

    /**
     * @param  Table  $table
     * @param  array<int|string, IP>  $peerAddresses
     * @return Row[]
     */
    private function readTable(string $context, array $table, array $peerAddresses): array
    {
        $fields = Arr::where(Arr::except($table, ['index_layout', 'fallback', 'ip_version']), fn ($value, $field) => ! str_ends_with((string) $field, '_map'));
        $oids = array_filter($fields, fn ($value) => is_string($value) && str_contains($value, '::'));
        $literals = array_diff_key($fields, $oids);
        if (empty($oids)) {
            return [];
        }

        // walk the column that tells us if there are any sessions first
        $first = $oids['remote_as'] ?? $oids['accepted'] ?? reset($oids);
        $walked = $this->walk($context, array_values(array_unique([$first, ...array_values($oids)])));

        $rows = [];
        foreach ($walked as $index => $values) {
            $row = $this->parseIndex($table['index_layout'] ?? [], explode('.', (string) $index));
            if ($row === null) {
                continue;
            }

            foreach ($oids as $field => $oid) {
                $value = $values[Str::after($oid, '::')] ?? null;
                if ($value !== null && $value !== '') {
                    $row[$field] = $table["{$field}_map"][$value] ?? $value;
                }
            }

            $row = $this->decode($row + $literals);
            if (! isset($row['address']) && isset($row['peer_index'], $peerAddresses[$row['peer_index']])) {
                $row['address'] = $peerAddresses[$row['peer_index']];
            }

            if (isset($table['ip_version']) && ($row['address'] ?? null)?->getFamily() != "ipv{$table['ip_version']}") {
                continue; // the table reports counters for every session, but only applies to this IP version
            }

            if (isset($row['address']) || isset($row['iface'])) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Split the index into its fields, null if it does not match the layout
     *
     * @param  string[]  $layout
     * @param  string[]  $parts
     * @return Row|null
     */
    private function parseIndex(array $layout, array $parts): ?array
    {
        $row = [];

        foreach ($layout as $position => $token) {
            if (in_array($token, self::ADDRESS_TOKENS)) {
                $trailing = $this->trailingParts(array_slice($layout, $position + 1), count($parts));
                $zone = null;
                $address = $token == 'ip' ? $this->shiftIp($parts, $trailing) : $this->shiftAddress($parts, $trailing, $zone);
                if ($address === null) {
                    return null;
                }

                if ($token != 'skip_address') {
                    $row['address'] = $address;
                    if ($zone) {
                        $row['iface'] = $zone; // the zone of a scoped address is its interface index
                    }
                }
            } elseif ($token == 'skip') {
                array_shift($parts);
            } else {
                $row[$token] = array_shift($parts); // vrf, peer_index, afi, safi
            }
        }

        return $row;
    }

    /**
     * How many index parts follow an address. When two addresses share an index, they are the same family.
     *
     * @param  string[]  $after  layout tokens after the address
     */
    private function trailingParts(array $after, int $remaining): int
    {
        $addresses = count(array_intersect($after, self::ADDRESS_TOKENS));
        $singles = count($after) - $addresses;

        return $singles + ($addresses ? intdiv($remaining - $singles, $addresses + 1) * $addresses : 0);
    }

    /**
     * Take an InetAddressType and InetAddress off the front of the index.
     * Vendors disagree on whether the address is prefixed with its length, so both are accepted.
     *
     * @param  string[]  $parts
     * @param  int|null  $zone  set to the zone of scoped (ipv4z, ipv6z) addresses
     */
    private function shiftAddress(array &$parts, int $trailing, ?int &$zone = null): ?IP
    {
        [$length, $addressLength] = self::ADDRESS_LENGTHS[(int) array_shift($parts)] ?? [null, null];
        $octets = $length === null ? null : $this->shiftOctets($parts, $trailing, [$length]);
        if ($octets === null) {
            return null;
        }

        $zone = array_reduce(array_slice($octets, $addressLength), fn (int $zone, string $octet) => $zone * 256 + (int) $octet, 0);

        return IP::fromSnmpString(implode('.', array_slice($octets, 0, $addressLength)), true);
    }

    /**
     * Take an address without a type off the front of the index, with or without a length
     *
     * @param  string[]  $parts
     */
    private function shiftIp(array &$parts, int $trailing): ?IP
    {
        $octets = $this->shiftOctets($parts, $trailing, [4, 16]);

        return $octets === null ? null : IP::fromSnmpString(implode('.', $octets), true);
    }

    /**
     * @param  string[]  $parts
     * @param  int[]  $lengths  valid index lengths
     * @return string[]|null
     */
    private function shiftOctets(array &$parts, int $trailing, array $lengths): ?array
    {
        $available = count($parts) - $trailing;
        if (in_array($available - 1, $lengths) && $parts[0] == $available - 1) {
            array_shift($parts);
            $available--;
        } elseif (! in_array($available, $lengths)) {
            return null;
        }

        return array_splice($parts, 0, $available);
    }

    /**
     * Convert snmp values to the types the fields expect
     *
     * @param  Row  $row
     * @return Row
     */
    private function decode(array $row): array
    {
        if (isset($row['last_error'])) {
            [$row['last_error_code'], $row['last_error_subcode']] = $this->lastError($row['last_error']) + [null, null];
            unset($row['last_error']);
        }

        foreach ($row as $field => $value) {
            if ($field == 'address' && ! $value instanceof IP) {
                $row[$field] = IP::fromHexString($value, true);
            } elseif ($field == 'local_address') {
                $row[$field] = IP::fromHexString($value, true)?->uncompressed();
            } elseif ($field == 'state') {
                $state = strtolower((string) $value);
                $row[$field] = in_array($state, self::STATES) ? $state : 'idle';
            } elseif ($field == 'afi') {
                $row[$field] = self::AFI[$value] ?? (string) $value;
            } elseif ($field == 'safi') {
                $row[$field] = self::SAFI[$value] ?? (string) $value;
            } elseif (in_array($field, self::INTEGERS)) {
                $row[$field] = is_numeric($value) ? Number::correctIntegerOverflow((int) $value) : null;
            } elseif (in_array($field, self::COUNTERS)) {
                $row[$field] = is_numeric($value) ? $this->low32((string) $value) : null;
            }
        }

        return array_filter($row, fn ($value) => $value !== null);
    }

    /**
     * The database and rrd expect 32 bit counters. 64 bit counters may not fit in an int,
     * so take the remainder digit by digit.
     */
    private function low32(string $value): int
    {
        if (! ctype_digit($value)) {
            return (Number::correctIntegerOverflow((int) $value) ?? 0) % 2 ** 32;
        }

        $low = 0;
        foreach (str_split($value) as $digit) {
            $low = ($low * 10 + (int) $digit) % 2 ** 32;
        }

        return $low;
    }

    /**
     * Split a two octet last error (code, subcode), displayed as hex or as raw octets
     *
     * @return array{0: int, 1: int}|array{}
     */
    private function lastError(string $value): array
    {
        $value = trim($value, " \"\0");

        if (preg_match('/^([0-9a-f]{1,2})[ :]?([0-9a-f]{2})$/i', $value, $matches)) {
            return [(int) hexdec($matches[1]), (int) hexdec($matches[2])];
        }

        if (strlen($value) == 2) {
            return [ord($value[0]), ord($value[1])];
        }

        return [];
    }
}
