<?php

// Polls strongSwan / OPNsense IPsec per-connection stats via JSON SNMP extend.
// Extend script: snmp/strongswan.py  (snmpd: `extend strongswan /usr/local/bin/strongswan.py`)

use LibreNMS\Data\Store\Rrd;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'strongswan';
$output = 'OK';

try {
    $data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // empty metrics + error

    return;
}

$tunnels = $data['tunnels'] ?? [];
$global = $data['global'] ?? [];

$metrics = [];
$labels = [];   // rrd instance -> human label (phase1 descr), shown in graphs/UI

// ---- per-tunnel (multi-instance) -------------------------------------------
$rrd_def = RrdDefinition::make()
    ->addDataset('state', 'GAUGE', 0, 1)
    ->addDataset('children', 'GAUGE', 0)
    ->addDataset('bytes_in', 'DERIVE', 0)
    ->addDataset('bytes_out', 'DERIVE', 0)
    ->addDataset('pkts_in', 'DERIVE', 0)
    ->addDataset('pkts_out', 'DERIVE', 0)
    ->addDataset('reestablishes', 'DERIVE', 0);

foreach ($tunnels as $t) {
    // prefix so no connection name can collide with the 'global' rrd, and match the rrd file name the UI reads back
    $tunnel = 'tun_' . Rrd::safeName((string) ($t['name'] ?? ''));
    if (isset($labels[$tunnel])) {
        continue; // two connection names that sanitize to the same rrd
    }
    $fields = [
        'state' => $t['state'] ?? 0,
        'children' => $t['children'] ?? 0,
        'bytes_in' => $t['bytes_in'] ?? 0,
        'bytes_out' => $t['bytes_out'] ?? 0,
        'pkts_in' => $t['pkts_in'] ?? 0,
        'pkts_out' => $t['pkts_out'] ?? 0,
        'reestablishes' => $t['reestablishes'] ?? 0,
    ];

    $rrd_name = ['app', $name, $app->app_id, $tunnel];
    $tags = ['name' => $tunnel, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    // application_metrics.metric is varchar(64): keep "<key>_reestablishes" within it for long connection names
    $metric_key = strlen($tunnel) > 50 ? 'tun_' . md5($tunnel) : $tunnel;
    $metrics[$metric_key] = $fields;

    $label = trim((string) ($t['descr'] ?? ''));
    if ($label === '') {
        $label = (string) ($t['name'] ?? '');
    }
    if (! empty($t['peer'])) {
        $label .= ' (' . $t['peer'] . ')';
    }
    $labels[$tunnel] = $label;
}

// ---- global counters (app-level single rrd) --------------------------------
if (! empty($global)) {
    $g_def = RrdDefinition::make()
        ->addDataset('ike_rekey', 'DERIVE', 0)
        ->addDataset('child_rekey', 'DERIVE', 0)
        ->addDataset('invalid', 'DERIVE', 0)
        ->addDataset('invalid_spi', 'DERIVE', 0);

    $g_fields = [
        'ike_rekey' => ($global['ike_rekey_init'] ?? 0) + ($global['ike_rekey_resp'] ?? 0),
        'child_rekey' => $global['child_rekey'] ?? 0,
        'invalid' => $global['invalid'] ?? 0,
        'invalid_spi' => $global['invalid_spi'] ?? 0,
    ];

    $rrd_name = ['app', $name, $app->app_id, 'global'];
    $tags = ['name' => 'global', 'app_id' => $app->app_id, 'rrd_def' => $g_def, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $g_fields);

    $metrics['global'] = $g_fields;
}

// persist the rrd instance -> human label map for the UI / graph legends
$app->data = ['labels' => $labels];

update_application($app, $output, $metrics);
