<?php

use LibreNMS\Agent\Unix\Mdadm\HtmlData;

require_once base_path('includes/html/debug-panel.inc.php');

// =============================================================================
// Standalone helper functions (pure / stateless)
// =============================================================================

function mdadm_badge(string $label, string $class, ?string $title = null): string
{
    $titleAttr = $title !== null && $title !== ''
        ? ' title="' . htmlspecialchars($title) . '"'
        : '';

    return '<span class="label label-' . $class . '"' . $titleAttr . '>' . htmlspecialchars($label) . '</span>';
}

/**
 * Sum diskio rates across all devices in an array from ucd-diskio RRDs.
 *
 * @return array<string, float>  Keys: 'read', 'written', 'reads', 'writes'
 */
function mdadm_diskio_rates(HtmlData $data, string $arrayName): array
{
    $hostname = (string) ($data->device['hostname'] ?? '');
    if ($hostname === '') {
        return [];
    }

    $devices = $data->arraysDevices[$arrayName] ?? [];
    $datasets = ['read', 'written', 'reads', 'writes'];
    $totals = [];

    foreach ($devices as $devId => $dev) {
        $path = trim((string) (is_array($dev) ? ($dev['path'] ?? $devId) : $devId));
        if ($path === '') {
            continue;
        }

        $candidates = array_values(array_unique([
            $path,
            ltrim((string) preg_replace('#^/dev/#', '', $path), '/'),
            basename($path),
        ]));

        foreach ($candidates as $candidate) {
            $rrdFile = App\Facades\Rrd::name($hostname, ['ucd_diskio', $candidate]);
            if (! App\Facades\Rrd::checkRrdExists($rrdFile)) {
                continue;
            }
            $point = App\Facades\Rrd::getLastRates($rrdFile, $datasets);
            if ($point === null) {
                break;
            }
            foreach ($datasets as $ds) {
                $v = $point->get($ds);
                if (is_numeric($v)) {
                    $totals[$ds] = ($totals[$ds] ?? 0.0) + (float) $v;
                }
            }
            break;
        }
    }

    return $totals;
}

// =============================================================================
// Debug helpers
// =============================================================================

function mdadm_debug_stored_data(int $appId, string $appName, string $hostname): array
{
    $stored = ['rrd' => []];

    if ($hostname === '' || $appId <= 0) {
        return $stored;
    }

    $baseRrdFile = App\Facades\Rrd::name($hostname, ['app', $appName, $appId]);
    $rrdDir = dirname($baseRrdFile);
    $basePrefix = pathinfo($baseRrdFile, PATHINFO_FILENAME);
    $matchingFiles = glob($rrdDir . '/' . $basePrefix . '*.rrd') ?: [];

    foreach ($matchingFiles as $rrdFile) {
        $filename = pathinfo($rrdFile, PATHINFO_FILENAME);
        $arrayName = '(app)';
        if (str_starts_with($filename, $basePrefix . '-')) {
            $arrayName = substr($filename, strlen($basePrefix) + 1) ?: '(app)';
        }

        $entry = [
            'array'             => $arrayName,
            'rrd_file'          => $rrdFile,
            'exists'            => App\Facades\Rrd::checkRrdExists($rrdFile),
            'expected_datasets' => ['active', 'spare', 'failed', 'degraded', 'mismatch', 'done_sectors', 'completed_pct', 'speed_bps'],
        ];

        if ($entry['exists']) {
            clearstatcache(true, $rrdFile);
            $entry['file'] = [
                'size_bytes'  => is_file($rrdFile) ? filesize($rrdFile) : null,
                'modified_at' => is_file($rrdFile) ? date('c', (int) filemtime($rrdFile)) : null,
                'age_seconds' => is_file($rrdFile) ? max(0, time() - (int) filemtime($rrdFile)) : null,
            ];

            $point = debug_rrd_last_point($rrdFile);
            $entry['last_update_ok'] = $point !== null;
            if ($point !== null) {
                $entry['last_update'] = [
                    'timestamp'     => $point->timestamp,
                    'timestamp_iso' => date('c', $point->timestamp),
                    'age_seconds'   => max(0, time() - $point->timestamp),
                    'data'          => $point->data,
                ];
            } else {
                $entry['last_update'] = null;
                $entry['last_update_note'] = 'RRD exists, but lastUpdate() returned null.';
            }
        }

        $stored['rrd'][] = $entry;
    }

    return $stored;
}

function mdadm_debug_render(int $appId, object $allSensors, string $appName, string $hostname): void
{
    // 1. DB tables — what to show
    $arrayRows = App\Models\MdadmArray::where('app_id', $appId)->get()->toArray();
    $driveRows = App\Models\MdadmDrive::where('app_id', $appId)->get()->toArray();

    // 2. RRD files — what to list
    $rrdEntries = mdadm_debug_stored_data($appId, $appName, $hostname)['rrd'];
    $stores = [];
    try {
        $datastore = app('Datastore');
        if (method_exists($datastore, 'getStores')) {
            $stores = array_values(array_map(
                static fn ($s) => (string) $s->getName(),
                (array) $datastore->getStores()
            ));
        }
    } catch (Throwable) {
    }

    $dsPreId = "mdadm-debug-ds-{$appId}";
    $dsJson = htmlspecialchars(json_encode(
        ['stores' => $stores, 'rrd' => $rrdEntries],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) ?: '{}');
    $rrdPanel = debug_panel(
        'Debug: RRD / Datastore',
        debug_rrd_files_panel($rrdEntries, $stores)
            . '<details style="margin-top:8px"><summary class="text-muted" style="cursor:pointer;font-size:12px">Raw JSON</summary>'
            . debug_pre($dsPreId, $dsJson) . '</details>',
        debug_toolbar($dsPreId, "mdadm-datastore-{$appId}.json")
    );

    // 3. Sensors — what to list
    $sensorColumns = ['sensor_oid', 'sensor_type', 'group', 'sensor_navigation', 'sensor_index', 'sensor_descr', 'current'];
    $sensorRows = $allSensors->map(fn ($s) => [
        'sensor_oid'        => $s->sensor_oid,
        'sensor_type'       => $s->sensor_type,
        'group'             => $s->group,
        'sensor_navigation' => $s->sensor_navigation,
        'sensor_index'      => $s->sensor_index,
        'sensor_descr'      => $s->sensor_descr,
        'current'           => $s->sensor_current,
    ])->toArray();

    debug_render('mdadm-debug-panels',
        debug_db_table_panel('DB: mdadm_arrays', $arrayRows, "mdadm-arrays-{$appId}.csv"),
        debug_db_table_panel('DB: mdadm_drives', $driveRows, "mdadm-drives-{$appId}.csv"),
        $rrdPanel,
        debug_sensors_panel(
            'Debug: Sensors (app:mdadm:*) &mdash; ' . count($sensorRows) . ' row(s)',
            $sensorColumns,
            $sensorRows,
            "mdadm-sensors-{$appId}.csv"
        )
    );
}

// =============================================================================
// Entry point
// =============================================================================

if (! isset($app, $device, $vars)
    || ! $app instanceof App\Models\Application
    || ! is_array($device)
    || ! is_array($vars)) {
    return;
}

$htmlData = HtmlData::forDevice($app, $device);

echo view('device.apps.mdadm', [
    'data'          => $htmlData,
    'app'           => $app,
    'device'        => $device,
    'selectedArray' => $vars['array'] ?? null,
])->render();
