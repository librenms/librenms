<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$snmp_extend_name = 'dhcpstats';
$name = 'dhcp-stats';
$app_id = $app['app_id'];
$options = '-Oqv';
$mib = 'NET-SNMP-EXTEND-MIB';
$oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.9.100.104.99.112.115.116.97.116.115';

$version = 1;
$output = 'OK';

try {
    $dhcp_data = json_app_get($device, $snmp_extend_name, 1);
    $dhcpstats = $dhcp_data['data'];
    $version = $dhcp_data['version'];
} catch (JsonAppMissingKeysException $e) {
    $dhcpstats = $e->getParsedJson();
    $output = 'ERROR';
} catch (JsonAppException $e) {
    $dhcpstats = snmp_walk($device, $oid, $options, $mib);
}

$version = intval($version);

if ($version == 1) {
    $output = 'LEGACY';
} elseif ($version == 2) {
    $output = 'OK';
} else {
    $output = 'UNSUPPORTED';
}

$metrics = [];
$category = 'stats';
if (intval($version) == 1) {
    [$dhcp_total, $dhcp_active, $dhcp_expired, $dhcp_released, $dhcp_abandoned, $dhcp_reset, $dhcp_bootp, $dhcp_backup, $dhcp_free] = explode("\n", $dhcpstats);
} elseif ($version == 2) {
    $lease_data = $dhcpstats['leases'];

    $dhcp_total = $lease_data['total'];
    $dhcp_active = $lease_data['active'];
    $dhcp_expired = $lease_data['expired'];
    $dhcp_released = $lease_data['released'];
    $dhcp_abandoned = $lease_data['abandoned'];
    $dhcp_reset = $lease_data['reset'];
    $dhcp_bootp = $lease_data['bootp'];
    $dhcp_backup = $lease_data['backup'];
    $dhcp_free = $lease_data['free'];
}

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('dhcp_total', 'GAUGE', 0)
    ->addDataset('dhcp_active', 'GAUGE', 0)
    ->addDataset('dhcp_expired', 'GAUGE', 0)
    ->addDataset('dhcp_released', 'GAUGE', 0)
    ->addDataset('dhcp_abandoned', 'GAUGE', 0)
    ->addDataset('dhcp_reset', 'GAUGE', 0)
    ->addDataset('dhcp_bootp', 'GAUGE', 0)
    ->addDataset('dhcp_backup', 'GAUGE', 0)
    ->addDataset('dhcp_free', 'GAUGE', 0);

$fields = [
    'dhcp_total'     => $dhcp_total,
    'dhcp_active'    => $dhcp_active,
    'dhcp_expired'   => $dhcp_expired,
    'dhcp_released'  => $dhcp_released,
    'dhcp_abandoned' => $dhcp_abandoned,
    'dhcp_reset'     => $dhcp_reset,
    'dhcp_bootp'     => $dhcp_bootp,
    'dhcp_backup'    => $dhcp_backup,
    'dhcp_free'      => $dhcp_free,
];
$metrics[$name . '_' . $category] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

if ($version == 2) {
    $category = 'pools';
    $pool_data = $dhcpstats['pools'];

    $rrd_def = RrdDefinition::make()
        ->addDataset('current', 'GAUGE', 0)
        ->addDataset('max', 'GAUGE', 0)
        ->addDataset('percent', 'GAUGE', 0);

    foreach ($pool_data as $data) {
        $dhcp_pool_name = $data['first_ip'] . '_-_' . $data['last_ip'];
        $dhcp_current = $data['cur'];
        $dhcp_max = $data['max'];
        $dhcp_percent = $data['percent'];

        $rrd_name = ['app', $name, $app_id, $category, $dhcp_pool_name];

        $fields = [
            'current' => $dhcp_current,
            'max'     => $dhcp_max,
            'percent' => $dhcp_percent,
        ];

        $metrics[$dhcp_pool_name . '_' . $category] = $fields;
        $tags = ['name' => $dhcp_pool_name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
        data_update($device, 'app', $tags, $fields);
    }

    $category = 'networks';
    $network_data = $dhcpstats['networks'];

    $rrd_def = RrdDefinition::make()
        ->addDataset('current', 'GAUGE', 0)
        ->addDataset('max', 'GAUGE', 0)
        ->addDataset('percent', 'GAUGE', 0);

    foreach ($network_data as $data) {
        $dhcp_network_name = str_replace('/', '_', $data['network']);
        $dhcp_current = $data['cur'];
        $dhcp_max = $data['max'];
        $dhcp_percent = $data['percent'] == 'nan' ? '0' : $data['percent'];

        $rrd_name = ['app', $name, $app_id, $category, $dhcp_network_name];

        $fields = [
            'current' => $dhcp_current,
            'max'     => $dhcp_max,
            'percent' => $dhcp_percent,
        ];

        $metrics[$dhcp_network_name . '_' . $category] = $fields;
        $tags = ['name' => $dhcp_network_name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
        data_update($device, 'app', $tags, $fields);
    }
}

if ($version == 1) {
    $app_state = $dhcp_active . '/' . $dhcp_total;
} else {
    $app_state = $dhcpstats['all_networks']['cur'] . '/' . $dhcpstats['all_networks']['max'];
}

update_application($app, $output, $metrics, $app_state);
