<?php

use App\Models\Eventlog;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'linux_pressure';

try {
    $returned = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    Log::info($name . ':' . $e->getCode() . ':' . $e->getMessage());
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

// avg10, avg60, and avg300 are the percent of that many seconds spent stalled.
// total is a counter of microseconds spent stalled.
$rrd_def = RrdDefinition::make()
    ->addDataset('avg10', 'GAUGE', 0, 100)
    ->addDataset('avg60', 'GAUGE', 0, 100)
    ->addDataset('avg300', 'GAUGE', 0, 100)
    ->addDataset('total', 'DERIVE', 0);

// The system wide pressure and each cgroup are all handled the same, differing
// only in what the RRDs and metrics are named and what averages become metrics.
$pressure_sources = [
    [
        'rrd_prefix' => 'system',
        'metric_prefix' => 'system',
        'metric_averages' => ['avg10', 'avg60', 'avg300'],
        'pressure' => $returned['data']['system'] ?? [],
    ],
];

// Only present if the extend is ran with -c or -r.
$cgroups = [];
foreach ($returned['data']['cgroups'] ?? [] as $cgroup => $cgroup_pressure) {
    // The longest RRD a cgroup can have is the one for memory some.
    $longest_rrd_filename = implode('-', ['app', $name, $app->app_id, 'cgroup___' . $cgroup . '___memory___some']) . '.rrd';
    if (strlen($longest_rrd_filename) > 255) {
        Log::info($name . ': skipping cgroup "' . $cgroup . '" as the name is too long for a RRD filename');
        continue;
    }

    $cgroups[] = $cgroup;
    $pressure_sources[] = [
        'rrd_prefix' => 'cgroup___' . $cgroup,
        'metric_prefix' => 'cgroup_' . $cgroup,
        // just avg300 so a pile of cgroups does not turn into a even bigger pile of metrics
        'metric_averages' => ['avg300'],
        'pressure' => $cgroup_pressure,
    ];
}

$metrics = [];

// One RRD per resource and stall type as not every kernel reports all of them.
// irq requires 6.1+ and only has full, while cpu only has full on 5.13+.
foreach ($pressure_sources as $pressure_source) {
    foreach (['cpu', 'io', 'memory', 'irq'] as $resource) {
        foreach (['some', 'full'] as $stall_type) {
            $pressure = $pressure_source['pressure'][$resource][$stall_type] ?? null;
            if (! is_array($pressure)) {
                continue;
            }

            $fields = [
                'avg10' => $pressure['avg10'] ?? null,
                'avg60' => $pressure['avg60'] ?? null,
                'avg300' => $pressure['avg300'] ?? null,
                'total' => $pressure['total'] ?? null,
            ];

            $rrd_name = ['app', $name, $app->app_id, $pressure_source['rrd_prefix'] . '___' . $resource . '___' . $stall_type];
            $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
            app('Datastore')->put($device, 'app', $tags, $fields);

            // total is left out as a raw counter is of no use for alerting
            foreach ($pressure_source['metric_averages'] as $average) {
                $metric_name = $pressure_source['metric_prefix'] . '_' . $resource . '_' . $stall_type . '_' . $average;
                // application_metrics.metric is a varchar(64), which a long cgroup name will not fit in
                if (strlen($metric_name) <= 64) {
                    $metrics[$metric_name] = $fields[$average];
                }
            }
        }
    }
}

// log any cgroups that have showed up or gone away since the last poll
sort($cgroups);
$old_cgroups = $app->data['cgroups'] ?? [];
$added_cgroups = array_diff($cgroups, $old_cgroups);
$removed_cgroups = array_diff($old_cgroups, $cgroups);
if (count($added_cgroups) > 0 || count($removed_cgroups) > 0) {
    $log_message = 'Linux Pressure Cgroup Change:';
    $log_message .= count($added_cgroups) > 0 ? ' Added ' . implode(',', $added_cgroups) : '';
    $log_message .= count($removed_cgroups) > 0 ? ' Removed ' . implode(',', $removed_cgroups) : '';
    Eventlog::log($log_message, $device['device_id'], 'application');
}

$app->data = ['cgroups' => $cgroups];

update_application($app, 'OK', $metrics);
