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

$col_pool_type      = 4;
$col_ports_inuse    = 5;
$col_sessions_inuse = 6;
$col_ports_avail    = 7;
$col_addr_avail     = 8;
$col_addr_inuse     = 9;

/**
 * Decode a jnxJsSrcNatStatsTable index suffix (everything after the column
 * number) into the pool name and raw address-type index value. Index
 * format is:
 *   <name_len>.<name ascii octets...>.<addr_type>.<address octets...>
 *
 * addr_type is carried through as the raw integer from the index rather
 * than mapped to a meaning (MIB declares ipv4(1)/ipv6(2), but a real walk
 * of a production SRX showed 0 for an IPv4 entry -- unconfirmed why, so
 * this only relies on the value being consistent per address family, not on
 * knowing what it means).
 */
function junos_nat_decode_index(string $index): ?array
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
    $name = implode('', array_map('chr', $name_octets));

    $addr_type = (int) array_shift($parts);

    return ['name' => $name, 'addr_type' => $addr_type];
}

function junos_nat_rrd_name(string $pool_name): string
{
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $pool_name);
}

/**
 * Human-readable label only -- for debug output and graph titles.
 * Never used for the RRD filename or aggregation key, since the raw
 * addr_type value observed on real hardware doesn't match the MIB's
 * documented ipv4(1)/ipv6(2) enum (see junos_nat_decode_index()).
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

$pools = [];
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

    $decoded = junos_nat_decode_index($index);
    if ($decoded === null) {
        continue;
    }

    // Pool names are only guaranteed unique within one address family on
    // a given device (and this table has no routing-instance/logical-
    // system index component at all, so same-name pools in different
    // RIs are indistinguishable here regardless -- a MIB limitation, not
    // fixable in this script). Key on name + addr_type so at least a v4
    // and v6 pool sharing a name don't get merged into one RRD.
    $pool_key = $decoded['name'] . '|' . $decoded['addr_type'];

    $pools[$pool_key] ??= [
        'name' => $decoded['name'],
        'addr_type' => $decoded['addr_type'],
        'ports_inuse' => 0,
        'sessions_inuse' => 0,
        'addr_avail' => 0,
        'addr_inuse' => 0,
        'ports_avail' => 0,
        'pool_type' => null,
    ];

    $int_val = (int) $value;

    switch ($col) {
        case $col_pool_type:
            $pools[$pool_key]['pool_type'] ??= $int_val;
            break;
        case $col_ports_inuse:
            $pools[$pool_key]['ports_inuse'] += $int_val;
            break;
        case $col_sessions_inuse:
            $pools[$pool_key]['sessions_inuse'] += $int_val;
            break;
        case $col_ports_avail:
            $pools[$pool_key]['ports_avail'] = max($pools[$pool_key]['ports_avail'], $int_val);
            break;
        case $col_addr_avail:
            $pools[$pool_key]['addr_avail'] = max($pools[$pool_key]['addr_avail'], $int_val);
            break;
        case $col_addr_inuse:
            $pools[$pool_key]['addr_inuse'] += $int_val;
            break;
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
