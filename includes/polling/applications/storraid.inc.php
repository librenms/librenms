<?php
/**
 * LibreNMS App Poller — storraid
 */

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'storraid';

// ── Fetch & decode JSON from SNMP extend ──────────────────────────────────────
try {
    $all_return = json_app_get($device, $name);
    $storraid   = $all_return['data'];
} catch (JsonAppMissingKeysException $e) {
    echo PHP_EOL . $name . ': Agent missing envelope keys — update storraid.py' . PHP_EOL;
    update_application($app, 'missing envelope keys', []);
    return;
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []);
    return;
}

$summary = $storraid['summary'] ?? [];

// ── Summary RRD ───────────────────────────────────────────────────────────────
$pd_errors = 0;
foreach ($storraid['physical_disks'] ?? [] as $pd) {
    $pd_errors += (int)($pd['media_errors'] ?? 0) + (int)($pd['other_errors'] ?? 0);
}

$summary_fields = [
    'overall_severity' => (int)($summary['overall_severity'] ?? 0),
    'ctrl_ok'          => (int)($summary['ctrl_ok']          ?? 0),
    'ctrl_warn'        => (int)($summary['ctrl_warn']         ?? 0),
    'ctrl_crit'        => (int)($summary['ctrl_crit']         ?? 0),
    'vd_ok'            => (int)($summary['vd_ok']             ?? 0),
    'vd_warn'          => (int)($summary['vd_warn']           ?? 0),
    'vd_crit'          => (int)($summary['vd_crit']           ?? 0),
    'pd_ok'            => (int)($summary['pd_ok']             ?? 0),
    'pd_warn'          => (int)($summary['pd_warn']           ?? 0),
    'pd_crit'          => (int)($summary['pd_crit']           ?? 0),
    'pd_count'         => (int)($summary['pd_count']          ?? 0),
    'vd_count'         => (int)($summary['vd_count']          ?? 0),
    'ctrl_count'       => (int)($summary['ctrl_count']        ?? 0),
    'pd_total_errors'  => $pd_errors,
];

$rrd_def = RrdDefinition::make()
    ->addDataset('overall_severity', 'GAUGE', 0, 2)
    ->addDataset('ctrl_ok',          'GAUGE', 0, 1000)
    ->addDataset('ctrl_warn',        'GAUGE', 0, 1000)
    ->addDataset('ctrl_crit',        'GAUGE', 0, 1000)
    ->addDataset('vd_ok',            'GAUGE', 0, 10000)
    ->addDataset('vd_warn',          'GAUGE', 0, 10000)
    ->addDataset('vd_crit',          'GAUGE', 0, 10000)
    ->addDataset('pd_ok',            'GAUGE', 0, 10000)
    ->addDataset('pd_warn',          'GAUGE', 0, 10000)
    ->addDataset('pd_crit',          'GAUGE', 0, 10000)
    ->addDataset('pd_total_errors',  'GAUGE', 0, 1000000);

$tags = [
    'name'     => $name,
    'app_id'   => $app->app_id,
    'rrd_def'  => $rrd_def,
    'rrd_name' => ['app', $name, $app->app_id],
];
app('Datastore')->put($device, 'app', $tags, $summary_fields);

$metrics = ['summary' => $summary_fields];

// ── Per-VD RRD ────────────────────────────────────────────────────────────────
foreach ($storraid['virtual_disks'] ?? [] as $vd) {
    $vd_id     = 'c' . $vd['controller'] . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $vd['id']);
    $vd_fields = [
        'severity' => (int)($vd['severity']    ?? 0),
        'progress' => ($vd['progress_pct'] !== null) ? (float)$vd['progress_pct'] : 0,
    ];

    $vd_def = RrdDefinition::make()
        ->addDataset('severity', 'GAUGE', 0, 2)
        ->addDataset('progress', 'GAUGE', 0, 100);

    $vd_tags = [
        'name'     => "{$name}_vd_{$vd_id}",
        'app_id'   => $app->app_id,
        'rrd_def'  => $vd_def,
        'rrd_name' => ['app', $name, $app->app_id, "vd_{$vd_id}"],
    ];
    app('Datastore')->put($device, 'app', $vd_tags, $vd_fields);

    $metrics["vd_{$vd_id}"] = $vd_fields;
}

// ── Per-PD RRD ────────────────────────────────────────────────────────────────
foreach ($storraid['physical_disks'] ?? [] as $pd) {
    $pd_id     = 'c' . $pd['controller'] . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $pd['eid_slot']);

    // pred_failure is stored as string ("0", "N/A", etc.) — coerce to int
    $pred_fail_raw = $pd['pred_failure'] ?? '0';
    $pred_fail_int = is_numeric($pred_fail_raw) ? (int)$pred_fail_raw : 0;

    $pd_fields = [
        'severity'     => (int)($pd['severity']    ?? 0),
        'media_errors' => (int)($pd['media_errors'] ?? 0),
        'other_errors' => (int)($pd['other_errors'] ?? 0),
        'pred_failure' => $pred_fail_int,
    ];

    $pd_def = RrdDefinition::make()
        ->addDataset('severity',     'GAUGE', 0, 2)
        ->addDataset('media_errors', 'GAUGE', 0, 1000000)
        ->addDataset('other_errors', 'GAUGE', 0, 1000000)
        ->addDataset('pred_failure', 'GAUGE', 0, 1000000);

    $pd_tags = [
        'name'     => "{$name}_pd_{$pd_id}",
        'app_id'   => $app->app_id,
        'rrd_def'  => $pd_def,
        'rrd_name' => ['app', $name, $app->app_id, "pd_{$pd_id}"],
    ];
    app('Datastore')->put($device, 'app', $pd_tags, $pd_fields);

    $metrics["pd_{$pd_id}"] = $pd_fields;
}

// ── Per-controller temperature RRD ────────────────────────────────────────────
foreach ($storraid['controllers'] ?? [] as $ctrl) {
    if (isset($ctrl['error'])) {
        continue;
    }
    $ctrl_id     = 'c' . (int)$ctrl['id'];
    $temp_c      = $ctrl['temperature'] ?? null;

    $ctrl_fields = [
        'temperature' => ($temp_c !== null) ? (int)$temp_c : 'U',
    ];

    $ctrl_def = RrdDefinition::make()
        ->addDataset('temperature', 'GAUGE', 0, 150);

    $ctrl_tags = [
        'name'     => "{$name}_ctrl_{$ctrl_id}",
        'app_id'   => $app->app_id,
        'rrd_def'  => $ctrl_def,
        'rrd_name' => ['app', $name, $app->app_id, "ctrl_{$ctrl_id}"],
    ];
    app('Datastore')->put($device, 'app', $ctrl_tags, $ctrl_fields);

    $metrics["ctrl_{$ctrl_id}"] = $ctrl_fields;
}

// ── Per-disk temperature RRD ─────────────────────────────────────────────────
foreach ($storraid['physical_disks'] ?? [] as $pd) {
    $temp_c = $pd['temperature'] ?? null;
    if ($temp_c === null) {
        continue;
    }
    $pd_id   = 'c' . (int)$pd['controller'] . '_'
             . preg_replace('/[^a-zA-Z0-9_]/', '_', $pd['eid_slot']);

    $pd_temp_fields = [
        'temperature' => (int)$temp_c,
    ];
    $pd_temp_def = RrdDefinition::make()
        ->addDataset('temperature', 'GAUGE', 0, 150);
    $pd_temp_tags = [
        'name'     => "{$name}_pdtemp_{$pd_id}",
        'app_id'   => $app->app_id,
        'rrd_def'  => $pd_temp_def,
        'rrd_name' => ['app', $name, $app->app_id, "pdtemp_{$pd_id}"],
    ];
    app('Datastore')->put($device, 'app', $pd_temp_tags, $pd_temp_fields);
    $metrics["pdtemp_{$pd_id}"] = $pd_temp_fields;
}

// ── Persist $app->data and finalise ───────────────────────────────────────────
// $app->data stores the full JSON payload for the display page to read.
$app->data = $storraid;

$status = "OK — {$summary['ctrl_count']} controllers, "
        . "{$summary['vd_count']} VDs, {$summary['pd_count']} PDs, "
        . "severity={$summary['overall_severity']}";

update_application($app, $status, $metrics);

echo "  {$name}: {$status}\n";
