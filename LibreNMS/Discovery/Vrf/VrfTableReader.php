<?php

/**
 * VrfTableReader.php
 *
 * Reads VRFs from snmp tables. Each table only needs a field to column map and its index layout.
 * MPLS-L3VPN-STD-MIB, then MPLS-VPN-MIB, are read when the os tables report no VRFs.
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

namespace LibreNMS\Discovery\Vrf;

use App\Models\Vrf;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Util\Number;
use SnmpQuery;

/**
 * A table maps fields to oids (or literal values) and describes its index.
 * Rows identify their VRF by the vendor VRF index (vrf) or only by name.
 * Rows with the same VRF index are merged, the first table to set a field wins.
 * Rows that only name their VRF are added to the VRF with that name.
 *
 * Tables are read in tiers, later tiers are only read when the earlier ones are missing data:
 * os tables, then each standard MIB while no VRF was found,
 * then the os fallback tables if no VRF or no interface membership was found.
 *
 * Index layout tokens, remaining index parts are ignored:
 * vrf: a single part VRF index, vrf_name: a length prefixed VRF name used as the VRF index,
 * name and context: length prefixed strings, ifIndex: a member interface, skip: an unused part.
 * Fields: name, rd, description, local_as, context, vrf (index as a value), ifIndex (member as a value)
 * and walk (a column only walked for its index). <field>_map replaces raw values,
 * skip_regex skips rows where a field matches and fallback marks the last tier.
 *
 * @phpstan-type Table array<string, mixed>
 * @phpstan-type Row array{vrf?: string, name?: string, rd?: string, description?: string, local_as?: int, context?: string, ifIndex?: int}
 * @phpstan-type VrfEntry array{vrf_oid: string, name: string, rd: string|null, description: string, local_as: int|null, context: string|null, ifIndexes: int[]}
 */
class VrfTableReader
{
    /** MPLS-L3VPN-STD-MIB (RFC 4382), indexed by the length prefixed VRF name */
    private const L3VPN_TABLES = [
        [
            'index_layout' => ['vrf_name'],
            'rd' => 'MPLS-L3VPN-STD-MIB::mplsL3VpnVrfRD',
            'description' => 'MPLS-L3VPN-STD-MIB::mplsL3VpnVrfDescription',
        ],
        [
            'index_layout' => ['vrf_name', 'ifIndex'],
            'walk' => 'MPLS-L3VPN-STD-MIB::mplsL3VpnIfVpnClassification',
        ],
    ];

    /**
     * MPLS-VPN-MIB, the draft MPLS-L3VPN-STD-MIB replaced. Devices implement an earlier
     * revision than the MIB file, without mplsVpnVrfVpnId, so the columns are named one off.
     */
    private const MPLS_VPN_TABLES = [
        [
            'index_layout' => ['vrf_name'],
            'rd' => 'MPLS-VPN-MIB::mplsVpnVrfDescription',
            'description' => 'MPLS-VPN-MIB::mplsVpnVrfVpnId',
        ],
        [
            'index_layout' => ['vrf_name', 'ifIndex'],
            'walk' => 'MPLS-VPN-MIB::mplsVpnIfLabelEdgeType',
        ],
    ];

    /** CISCO-VRF-MIB covers routers without MPLS, C8xx routers only report interfaces there */
    private const CISCO_TABLES = [
        [
            'index_layout' => ['context'],
            'name' => 'CISCO-CONTEXT-MAPPING-MIB::cContextMappingVrfName',
        ],
        [
            'index_layout' => ['vrf'],
            'fallback' => true,
            'name' => 'CISCO-VRF-MIB::cvVrfName',
            'skip_regex' => ['name' => '/Platform_iVRF/'],
        ],
        [
            'index_layout' => ['vrf', 'ifIndex'],
            'fallback' => true,
            'walk' => 'CISCO-VRF-MIB::cvVrfInterfaceType',
        ],
    ];

    /** @var array<string, Table[]> */
    private const OS_TABLES = [
        'arista_eos' => [
            [
                'index_layout' => ['vrf_name'],
                'rd' => 'ARISTA-VRF-MIB::aristaVrfRouteDistinguisher',
                'rd_map' => ['INVALID' => ''],
            ],
            [
                'index_layout' => ['ifIndex'],
                'name' => 'ARISTA-VRF-MIB::aristaVrfIfMembership',
            ],
        ],
        'cumulus' => [
            [
                'index_layout' => ['vrf'],
                'name' => 'CUMULUS-BGPVRF-MIB::bgpVrfName',
            ],
        ],
        'firebrick' => [
            [
                // routing tables are only listed by the BGP sessions in them, table 0 is the default
                'vrf' => 'FIREBRICK-BGP-MIB::fbBgpPeerTableId',
                'name' => 'FIREBRICK-BGP-MIB::fbBgpPeerTableId',
                'skip_regex' => ['vrf' => '/^0$/'],
            ],
        ],
        'ios' => self::CISCO_TABLES,
        'iosxe' => self::CISCO_TABLES,
        'iosxr' => self::CISCO_TABLES,
        'nxos' => [
            ...self::CISCO_TABLES,
            [
                // NX-OS only fills the VRF MIBs with MPLS L3VPN, VRFs with BGP sessions are listed here
                'index_layout' => ['vrf'],
                'fallback' => true,
                'name' => 'CISCO-BGP4-MIB::cbgpPeer3VrfName',
                'skip_regex' => ['name' => '/^default$/i'],
            ],
        ],
        'timos' => [
            [
                'index_layout' => ['vrf'],
                'name' => 'TIMETRA-VRTR-MIB::vRtrName',
                'description' => 'TIMETRA-VRTR-MIB::vRtrDescription',
                'local_as' => 'TIMETRA-VRTR-MIB::vRtrAS4Byte',
                'rd' => 'TIMETRA-VRTR-MIB::vRtrRouteDistinguisher',
            ],
            [
                'index_layout' => ['vrf', 'ifIndex'],
                'walk' => 'TIMETRA-VRTR-MIB::vRtrIfName',
            ],
        ],
        'vos' => [
            [
                'index_layout' => ['ifIndex'],
                'name' => 'VERSA-IF-MIB::versaIfVIfVrfName',
            ],
        ],
    ];

    /** fields stored for a VRF, other fields only exist to walk a table */
    private const FIELDS = ['vrf', 'name', 'rd', 'description', 'local_as', 'context', 'ifIndex'];

    /** @var Table[] */
    private readonly array $tables;
    /** @var Table[] */
    private readonly array $fallbackTables;

    /**
     * @param  Table[]  $tables  vendor tables, read before the standard MIBs
     */
    public function __construct(array $tables = [])
    {
        $this->tables = array_values(array_filter($tables, fn (array $table) => ! ($table['fallback'] ?? false)));
        $this->fallbackTables = array_values(array_filter($tables, fn (array $table) => $table['fallback'] ?? false));
    }

    public static function forOs(string $os): self
    {
        return new self(self::OS_TABLES[$os] ?? []);
    }

    /**
     * VRFs of the device, keyed by vrf_oid
     *
     * @return array<array-key, VrfEntry>
     */
    public function read(): array
    {
        $tiers = [$this->readTables($this->tables)];

        foreach ([self::L3VPN_TABLES, self::MPLS_VPN_TABLES] as $standardTables) {
            if (! $this->identifiesVrfs($tiers)) {
                $tiers[] = $this->readTables($standardTables);
            }
        }

        if (! empty($this->fallbackTables) && (! $this->identifiesVrfs($tiers) || ! $this->hasMembers($tiers))) {
            $tiers[] = $this->readTables($this->fallbackTables);
        }

        return $this->assemble($tiers);
    }

    /**
     * Combine the rows of each tier into VRFs.
     * Within a tier, rows are merged by VRF index. Across tiers, and for rows without an index, by name.
     *
     * @param  array<int, Row[]>  $tiers
     * @return array<array-key, VrfEntry>
     */
    public function assemble(array $tiers): array
    {
        /** @var array<array-key, VrfEntry> $vrfs */
        $vrfs = [];
        $named = [];

        foreach ($tiers as $rows) {
            $earlier = $vrfs; // VRFs found by an earlier tier
            $indexed = [];
            foreach ($rows as $row) {
                if (isset($row['vrf'])) {
                    $indexed[$row['vrf']] = $this->merge($indexed[$row['vrf']] ?? $this->newVrf($row['vrf']), $row);
                } elseif (isset($row['name'])) {
                    $named[] = $row;
                }
            }

            foreach ($indexed as $vrf) {
                if ($vrf['name'] === '') {
                    continue; // only membership was reported, the VRF is unknown
                }

                $existing = $this->findByName($earlier, $vrf['name']);
                if ($existing === null) {
                    $vrfs[$vrf['vrf_oid']] ??= $vrf;
                } else {
                    // an earlier tier found the VRF, keep its index and add what is missing
                    $vrfs[$existing] = $this->merge($vrfs[$existing], $vrf);
                }
            }
        }

        foreach ($named as $row) {
            $existing = $this->findByName($vrfs, $row['name']);
            if ($existing !== null) {
                $vrfs[$existing] = $this->merge($vrfs[$existing], $row);
            } elseif (! Vrf::isDefaultName($row['name'])) {
                // the device only references this VRF by name, so the name is its identifier
                $vrfs[$row['name']] = $this->merge($this->newVrf($row['name']), $row);
            }
        }

        return $vrfs;
    }

    /**
     * Split the index into its fields, null if it does not match the layout.
     * Index parts after the layout are ignored.
     *
     * @param  string[]  $layout
     * @param  string[]  $parts
     * @return array<string, string>|null
     */
    public function parseIndex(array $layout, array $parts): ?array
    {
        $row = [];

        foreach ($layout as $token) {
            if (in_array($token, ['vrf_name', 'name', 'context'])) {
                $string = $this->shiftString($parts);
                if ($string === null) {
                    return null;
                }

                [$encoded, $value] = $string;
                if ($token == 'vrf_name') {
                    $row['vrf'] = $encoded;
                    $row['name'] = $value;
                } else {
                    $row[$token] = $value;
                }
            } elseif (empty($parts)) {
                return null;
            } elseif ($token == 'skip') {
                array_shift($parts);
            } else {
                $row[$token] = array_shift($parts);
            }
        }

        return $row;
    }

    /**
     * Route distinguishers are reported as text, as text with a trailing null or as the 8 octet RFC 4364 value
     */
    public function decodeRouteDistinguisher(string $value): ?string
    {
        $value = trim($value, ' "');
        $octets = preg_match('/^[0-9a-f]{2}( [0-9a-f]{2})+$/i', $value) ? (string) hex2bin(str_replace(' ', '', $value)) : $value;

        $text = trim($octets, " \0");
        if ($text !== '' && ctype_print($text)) {
            return $text;
        }

        return strlen($octets) == 8 ? $this->decodeBinaryRouteDistinguisher($octets) : null;
    }

    /**
     * Walk a table and group the values by numeric index.
     * The first oid is walked alone so devices without the table only cost a single walk.
     *
     * @param  string[]  $oids
     * @return array<string, array<string, string>>
     */
    protected function walk(array $oids): array
    {
        $first = SnmpQuery::hideMib()->enumStrings()->numericIndex()->walk(array_shift($oids));
        if (! $first->isValid()) {
            return [];
        }

        $rows = $first->valuesByIndex();
        if (! empty($oids)) {
            SnmpQuery::hideMib()->enumStrings()->numericIndex()->walk($oids)->valuesByIndex($rows);
        }

        return $rows;
    }

    /**
     * @param  array<int, Row[]>  $tiers
     */
    private function identifiesVrfs(array $tiers): bool
    {
        foreach ($tiers as $rows) {
            foreach ($rows as $row) {
                if (isset($row['vrf'], $row['name'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<int, Row[]>  $tiers
     */
    private function hasMembers(array $tiers): bool
    {
        foreach ($tiers as $rows) {
            if (! empty(array_column($rows, 'ifIndex'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Table[]  $tables
     * @return Row[]
     */
    private function readTables(array $tables): array
    {
        $rows = [];
        foreach ($tables as $table) {
            array_push($rows, ...$this->readTable($table));
        }

        return $rows;
    }

    /**
     * @param  Table  $table
     * @return Row[]
     */
    private function readTable(array $table): array
    {
        $fields = Arr::where(
            Arr::except($table, ['index_layout', 'fallback', 'skip_regex']),
            fn ($value, $field) => ! str_ends_with((string) $field, '_map'),
        );
        $oids = array_filter($fields, fn ($value) => is_string($value) && str_contains($value, '::'));
        $literals = array_intersect_key(array_diff_key($fields, $oids), array_flip(self::FIELDS));
        if (empty($oids)) {
            return [];
        }

        // the identifying column tells us if the table exists
        $first = $oids['name'] ?? $oids['vrf'] ?? reset($oids);
        $walked = $this->walk(array_values(array_unique([$first, ...array_values($oids)])));

        $skipped = [];
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

            if ($this->skip($table['skip_regex'] ?? [], $row)) {
                if (isset($row['vrf'])) {
                    $skipped[$row['vrf']] = true;
                }

                continue;
            }

            if (isset($row['vrf']) || isset($row['name'])) {
                $rows[] = $row;
            }
        }

        // drop the remaining rows of skipped VRFs
        return array_values(array_filter($rows, fn (array $row) => ! isset($row['vrf'], $skipped[$row['vrf']])));
    }

    /**
     * @param  array<string, string>  $regexes  field to regex
     * @param  Row  $row
     */
    private function skip(array $regexes, array $row): bool
    {
        foreach ($regexes as $field => $regex) {
            if (isset($row[$field]) && preg_match($regex, (string) $row[$field])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert snmp values to the types the fields expect, drop fields that are only walked
     *
     * @param  array<string, mixed>  $row
     * @return Row
     */
    private function decode(array $row): array
    {
        $decoded = [];

        foreach (array_intersect_key($row, array_flip(self::FIELDS)) as $field => $value) {
            if ($field == 'rd') {
                $value = $this->decodeRouteDistinguisher((string) $value);
            } elseif ($field == 'local_as') {
                $value = is_numeric($value) ? Number::correctIntegerOverflow((int) $value) ?: null : null; // AS 0 is not set
            } elseif ($field == 'ifIndex') {
                $value = is_numeric($value) && $value > 0 ? (int) $value : null;
            } else {
                $value = trim((string) $value, " \"\0");
                $value = $value === '' ? null : $value;
            }

            if ($value !== null) {
                $decoded[$field] = $value;
            }
        }

        /** @var Row $decoded */
        return $decoded;
    }

    /**
     * @return VrfEntry
     */
    private function newVrf(string $vrfOid): array
    {
        return ['vrf_oid' => $vrfOid, 'name' => '', 'rd' => null, 'description' => '', 'local_as' => null, 'context' => null, 'ifIndexes' => []];
    }

    /**
     * Fill missing fields of the VRF and add interfaces
     *
     * @param  VrfEntry  $vrf
     * @param  Row|VrfEntry  $data
     * @return VrfEntry
     */
    private function merge(array $vrf, array $data): array
    {
        foreach (['name', 'rd', 'description', 'local_as', 'context'] as $field) {
            if (($vrf[$field] === null || $vrf[$field] === '') && isset($data[$field])) {
                $vrf[$field] = $data[$field];
            }
        }

        $ifIndexes = $data['ifIndexes'] ?? (isset($data['ifIndex']) ? [$data['ifIndex']] : []);
        $vrf['ifIndexes'] = array_values(array_unique([...$vrf['ifIndexes'], ...$ifIndexes]));

        return $vrf;
    }

    /**
     * @param  array<array-key, VrfEntry>  $vrfs
     */
    private function findByName(array $vrfs, string $name): int|string|null
    {
        foreach ($vrfs as $key => $vrf) {
            if ($vrf['name'] === $name) {
                return $key;
            }
        }

        foreach ($vrfs as $key => $vrf) {
            if (strcasecmp($vrf['name'], $name) == 0) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Take a length prefixed string off the front of the index
     *
     * @param  string[]  $parts
     * @return array{0: string, 1: string}|null the encoded index parts and the string
     */
    private function shiftString(array &$parts): ?array
    {
        $length = (int) ($parts[0] ?? 0);
        if ($length < 1 || count($parts) <= $length) {
            return null;
        }

        $encoded = array_splice($parts, 0, $length + 1);

        return [implode('.', $encoded), implode('', array_map(fn (string $char) => chr((int) $char), array_slice($encoded, 1)))];
    }

    /**
     * A 2 octet type, then administrator and assigned number subfields (RFC 4364 4.2)
     */
    private function decodeBinaryRouteDistinguisher(string $octets): ?string
    {
        if (trim($octets, "\0") === '') {
            return null;
        }

        $type = unpack('n', substr($octets, 0, 2))[1];
        $value = substr($octets, 2);

        if ($type == 0) {
            $parts = unpack('nadmin/Nnumber', $value);

            return "{$parts['admin']}:{$parts['number']}";
        }

        if ($type == 1) {
            return inet_ntop(substr($value, 0, 4)) . ':' . unpack('n', substr($value, 4))[1];
        }

        if ($type == 2) {
            $parts = unpack('Nadmin/nnumber', $value);

            return "{$parts['admin']}:{$parts['number']}";
        }

        return null;
    }
}
