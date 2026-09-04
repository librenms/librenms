<?php

/**
 * junos-nat-pool.inc.php
 *
 * Polls jnxJsSrcNatStatsTable from Juniper SRX devices and stores
 * per-pool port/session/address usage in RRD files, one RRD per pool.
 *
 * Enable with: lnms config:set poller_modules.junos-nat-pool true
 */

use LibreNMS\RRD\RrdDefinition;

if ($device['os'] !== 'junos') {
    return;
}

$base_oid = '.1.3.6.1.4.1.2636.3.39.1.7.1.1.4.1';

$col_addr_type      = 2;
$col_pool_type      = 4;
$col_ports_inuse    = 5;
$col_sessions_inuse = 6;
$col_ports_avail    = 7;
$col_addr_avail     = 8;
$col_addr_inuse     = 9;

/**
 * Decode a jnxJsSrcNatStatsTable index suffix (everything after the column
 * number) into the pool name. Index format is:
 *   <name_len>.<name ascii octets...>.<addr_type>.<address octets...>
 *
 * Only the name is extracted here. The <addr_type> segment embedded in
 * the index is NOT used for address family -- a real walk of a
 * production SRX showed it as 0 on every single row regardless of
 * family, while the jnxJsNatSrcXlatedAddrType column (2) correctly
 * reported 1 (ipv4) for those same rows. The column value is the only
 * reliable source of family; see the two-pass aggregation below.
 */
function junos_nat_decode_pool_name(string $index): ?string
{
    $parts = explode('.', $index);
    if (count($parts) < 2) {
        return null;
    }

    $name_len = (int) array_shift($parts);
    if ($name_len <= 0 || count($parts) < $name_len + 1) {
        return null;
    }

    $name_octets = array_splice($parts, 0, $name_len);

    return implode('', array_map('chr', $name_octets));
}

function junos_nat_rrd_name(string $pool_name): string
{
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $pool_name);
}

/**
 * Human-readable label only -- for debug output and graph titles.
 * Matches the jnxJsNatSrcXlatedAddrType column's documented enum
 * (ipv4(1)/ipv6(2)); anything else is passed through as "familyN".
 */
function junos_nat_family_label(int $addr_type): string
{
    return match ($addr_type) {
        1 => 'v4',
        2 => 'v6',
        default => "family$addr_type",
    };
}

d_echo("Polling jnxJsSrcNatStatsTable ($base_oid)\n");

$response = \SnmpQuery::numeric()->walk($base_oid);

if (! $response->isValid(true)) {
    d_echo('No NAT pool data returned: ' . $response->getErrorMessage() . "\n");

    return;
}

// First pass: bucket every column value by its raw per-row index string,
// so all columns for the same (pool, address) row end up together
// before we look at column 2 to find the real address family. Table
// walks are column-major (all rows of column 1, then all of column 2,
// etc.), so column 2 for a row isn't necessarily seen before column 5/6
// for that same row -- can't determine family and aggregate in one pass.
$rows = [];
$prefix = $base_oid . '.';
$prefix_len = strlen($prefix);

foreach ($response->values() as $oid => $value) {
    if (! str_starts_with($oid, $prefix)) {
        continue;
    }

    $suffix = substr($oid, $prefix_len);
    $dot = strpos($suffix, '.');
    if ($dot === false) {
        continue;
    }

    $col = (int) substr($suffix, 0, $dot);
    $index = substr($suffix, $dot + 1);

    $rows[$index][$col] = $value;
}

if (empty($rows)) {
    d_echo("No NAT pool rows decoded\n");

    return;
}

// Second pass: decode the pool name, read the true address family from
// column 2, and aggregate into per (name, addr_type) pools. Pool names
// are only guaranteed unique within one address family on a given device
// -- and this table has no routing-instance/logical-system index
// component at all, so same-name pools in different RIs are
// indistinguishable here regardless, a MIB limitation not fixable in
// this script.
$pools = [];

foreach ($rows as $index => $row) {
    $pool_name = junos_nat_decode_pool_name($index);
    if ($pool_name === null) {
        continue;
    }

    if (! isset($row[$col_addr_type])) {
        d_echo("Skipping row for pool '$pool_name': no address-type (column 2) value\n");

        continue;
    }
    $addr_type = (int) $row[$col_addr_type];

    $pool_key = $pool_name . '|' . $addr_type;

    $pools[$pool_key] ??= [
        'name' => $pool_name,
        'addr_type' => $addr_type,
        'ports_inuse' => 0,
        'sessions_inuse' => 0,
        'addr_avail' => 0,
        'addr_inuse' => 0,
        'ports_avail' => 0,
        'pool_type' => null,
    ];

    if (isset($row[$col_pool_type])) {
        $pools[$pool_key]['pool_type'] ??= (int) $row[$col_pool_type];
    }
    if (isset($row[$col_ports_inuse])) {
        $pools[$pool_key]['ports_inuse'] += (int) $row[$col_ports_inuse];
    }
    if (isset($row[$col_sessions_inuse])) {
        $pools[$pool_key]['sessions_inuse'] += (int) $row[$col_sessions_inuse];
    }
    if (isset($row[$col_ports_avail])) {
        $pools[$pool_key]['ports_avail'] = max($pools[$pool_key]['ports_avail'], (int) $row[$col_ports_avail]);
    }
    if (isset($row[$col_addr_avail])) {
        $pools[$pool_key]['addr_avail'] = max($pools[$pool_key]['addr_avail'], (int) $row[$col_addr_avail]);
    }
    if (isset($row[$col_addr_inuse])) {
        $pools[$pool_key]['addr_inuse'] += (int) $row[$col_addr_inuse];
    }
}

if (empty($pools)) {
    d_echo("No pools decoded from NAT table\n");

    return;
}

d_echo('Found ' . count($pools) . ' NAT pool(s): ' . implode(', ', array_map(
    fn ($data) => $data['name'] . ' (' . junos_nat_family_label($data['addr_type']) . ')',
    $pools
)) . "\n");

foreach ($pools as $data) {
    $pool_name = $data['name'];
    $rrd_name = ['junos', 'nat-pool', junos_nat_rrd_name($pool_name), (string) $data['addr_type']];

    $rrd_def = RrdDefinition::make()
        ->addDataset('ports_inuse', 'GAUGE', 0, 125000000)
        ->addDataset('sessions_inuse', 'GAUGE', 0, 125000000)
        ->addDataset('addr_inuse', 'GAUGE', 0, 65536)
        ->addDataset('addr_avail', 'GAUGE', 0, 65536)
        ->addDataset('ports_avail', 'GAUGE', 0, 125000000);

    $fields = [
        'ports_inuse' => $data['ports_inuse'],
        'sessions_inuse' => $data['sessions_inuse'],
        'addr_inuse' => $data['addr_inuse'],
        'addr_avail' => $data['addr_avail'],
        'ports_avail' => $data['ports_avail'],
    ];

    $tags = [
        'rrd_name' => $rrd_name,
        'rrd_def' => $rrd_def,
    ];

    app('Datastore')->put($device, 'junos-nat-pool', $tags, $fields);

    d_echo(sprintf(
        "  %-32s (%s) ports_inuse=%-6d sessions_inuse=%-6d addr_inuse=%d/%d\n",
        $pool_name,
        junos_nat_family_label($data['addr_type']),
        $data['ports_inuse'],
        $data['sessions_inuse'],
        $data['addr_inuse'],
        $data['addr_avail']
    ));
}
