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
 * number) into the pool name. Index format is:
 *   <name_len>.<name ascii octets...>.<addr_type>.<address octets...>
 */
function junos_nat_decode_pool_name(string $index): ?string
{
    $parts = explode('.', $index);
    if (count($parts) < 2) {
        return null;
    }

    $name_len = (int) array_shift($parts);
    if ($name_len <= 0 || count($parts) < $name_len) {
        return null;
    }

    $name_octets = array_splice($parts, 0, $name_len);

    return implode('', array_map('chr', $name_octets));
}

function junos_nat_rrd_name(string $pool_name): string
{
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $pool_name);
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

    $pool_name = junos_nat_decode_pool_name($index);
    if ($pool_name === null) {
        continue;
    }

    $pools[$pool_name] ??= [
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
            $pools[$pool_name]['pool_type'] ??= $int_val;
            break;
        case $col_ports_inuse:
            $pools[$pool_name]['ports_inuse'] += $int_val;
            break;
        case $col_sessions_inuse:
            $pools[$pool_name]['sessions_inuse'] += $int_val;
            break;
        case $col_ports_avail:
            $pools[$pool_name]['ports_avail'] = max($pools[$pool_name]['ports_avail'], $int_val);
            break;
        case $col_addr_avail:
            $pools[$pool_name]['addr_avail'] = max($pools[$pool_name]['addr_avail'], $int_val);
            break;
        case $col_addr_inuse:
            $pools[$pool_name]['addr_inuse'] += $int_val;
            break;
    }
}

if (empty($pools)) {
    d_echo("No pools decoded from NAT table\n");

    return;
}

d_echo('Found ' . count($pools) . ' NAT pool(s): ' . implode(', ', array_keys($pools)) . "\n");

foreach ($pools as $pool_name => $data) {
    $rrd_name = ['junos', 'nat-pool', junos_nat_rrd_name($pool_name)];

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
        "  %-32s ports_inuse=%-6d sessions_inuse=%-6d addr_inuse=%d/%d\n",
        $pool_name,
        $data['ports_inuse'],
        $data['sessions_inuse'],
        $data['addr_inuse'],
        $data['addr_avail']
    ));
}
