<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'haproxy';

try {
    $data = json_app_get($device, $name)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . "$name:{$e->getCode()}:{$e->getMessage()}" . PHP_EOL;
    update_application($app, "{$e->getCode()}:{$e->getMessage()}");

    return;
}

$haproxy_shared = require base_path('includes/haproxy-shared.inc.php');
$groups = $haproxy_shared['groups'];
$no_total = $haproxy_shared['no_total'];

// numeric representation of the status string
$status_codes = [
    'DOWN' => 0,
    'UP' => 1,
    'OPEN' => 1,
    'NOLB' => 2,
    'MAINT' => 3,
    'DRAIN' => 4,
    'no check' => 5,
    'FULL' => 6,
    'STOP' => 7,
];

// HAProxy type => short name used for RRDs and metrics
$types = [
    0 => 'fe',
    1 => 'be',
    2 => 'srv',
    3 => 'li',
];

// build the RRD definitions for each group once, for both items and totals
$rrd_defs = [];
$total_rrd_defs = [];
foreach ($groups as $group => $group_def) {
    $rrd_defs[$group] = RrdDefinition::make();
    $total_rrd_defs[$group] = RrdDefinition::make();
    foreach ($group_def['fields'] as [$ds, $ds_type]) {
        $rrd_defs[$group]->addDataset($ds, $ds_type, 0);
        if (! in_array($ds, $no_total)) {
            $total_rrd_defs[$group]->addDataset($ds, $ds_type, 0);
        }
    }
}

$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
];

$metrics = [];
$app_data = [
    'frontends' => [],
    'backends' => [],
    'servers' => [],
    'listeners' => [],
    // gated group => [item RRD name => true], for ones that have been seen as active
    'gated' => [],
];
$prev_gated = $app->data['gated'] ?? [];

$counts = [];
foreach (['fe', 'be', 'srv', 'li'] as $type) {
    foreach (['total', 'up', 'down', 'maint', 'drain', 'nolb', 'other'] as $state) {
        $counts[$type . '_' . $state] = 0;
    }
}

// sums used for the totals, done per group and type and picked from after
$sums = [];

foreach ($data as $pxname => $services) {
    foreach ($services as $svname => $row) {
        if (! isset($types[$row['type'] ?? null])) {
            continue;
        }
        $type = $types[$row['type']];
        $pxname = (string) $pxname;
        $svname = (string) $svname;

        // the status can have stuff like 'UP 1/3' or 'MAINT (via x/y)' for transitional states
        $status_string = $row['status'] ?? '';
        $status_base = $status_string === 'no check' ? 'no check' : preg_replace('/\s.*$/', '', $status_string);
        $status_base = $status_base === 'STOPPED' ? 'STOP' : $status_base;
        $status = $status_codes[$status_base] ?? null;

        $counts[$type . '_total']++;
        $state = match ($status_base) {
            'UP', 'OPEN', 'no check' => 'up',
            'DOWN' => 'down',
            'MAINT' => 'maint',
            'DRAIN' => 'drain',
            'NOLB' => 'nolb',
            default => 'other',
        };
        $counts[$type . '_' . $state]++;

        $is_http = ($row['mode'] ?? null) == 'http';
        $id = ($type == 'fe' || $type == 'be') ? $pxname : $pxname . '_-_' . $svname;
        $item_rrd = $type . '_-_' . $id;

        $item_fields = [];
        foreach ($groups as $group => $group_def) {
            if (! in_array($type, $group_def['types']) || ($group_def['http'] && ! $is_http)) {
                continue;
            }

            $rrd_fields = [];
            foreach ($group_def['fields'] as $field => [$ds, $ds_type]) {
                $rrd_fields[$ds] = $field == 'status' ? $status : ($row[$field] ?? null);
            }

            // gated groups are skipped till they have something besides zeros
            if ($group_def['gated']) {
                if (! isset($prev_gated[$group][$item_rrd]) && empty(array_filter($rrd_fields))) {
                    continue;
                }
                $app_data['gated'][$group][$item_rrd] = true;
            }

            $tags['rrd_def'] = $rrd_defs[$group];
            $tags['rrd_name'] = ['app', $name, $app->app_id, $item_rrd . '_-_' . $group];
            app('Datastore')->put($device, 'app', $tags, $rrd_fields);

            if ($group_def['totals'] && ($type == 'fe' || $type == 'be')) {
                $sums[$group][$type] ??= [];
                foreach ($rrd_fields as $ds => $value) {
                    if (! in_array($ds, $no_total) && is_numeric($value)) {
                        $sums[$group][$type][$ds] = ($sums[$group][$type][$ds] ?? 0) + $value;
                    }
                }
            }

            $item_fields = array_merge($item_fields, $rrd_fields);
        }

        // not graphed as -1 means never, but handy for alerting
        if ($type == 'be' || $type == 'srv') {
            $item_fields['lastsess'] = $row['lastsess'] ?? null;
        }

        // the metric column is limited to 64 chars, so skip any that would be too long
        foreach ($item_fields as $ds => $value) {
            $metric = $type . '_' . $id . '_' . $ds;
            if (! is_null($value) && strlen($metric) <= 64) {
                $metrics[$metric] = $value;
            } elseif (! is_null($value)) {
                d_echo('HAProxy metric "' . $metric . '" is longer than 64 chars, skipping');
            }
        }

        $info = [
            'status' => $status_string,
            'mode' => $row['mode'] ?? null,
        ];
        if ($type == 'srv' || $type == 'be') {
            $info['lastchg'] = $row['lastchg'] ?? null;
            $info['lastsess'] = $row['lastsess'] ?? null;
            $info['act'] = $row['act'] ?? null;
            $info['bck'] = $row['bck'] ?? null;
            $info['weight'] = $row['weight'] ?? null;
        }
        if ($type == 'srv') {
            $info['addr'] = $row['addr'] ?? null;
            $info['check_status'] = $row['check_status'] ?? null;
            $info['check_desc'] = $row['check_desc'] ?? null;
            $info['check_code'] = $row['check_code'] ?? null;
            $info['check_health'] = $row['check_health'] ?? null;
            $info['check_rise'] = $row['check_rise'] ?? null;
            $info['check_fall'] = $row['check_fall'] ?? null;
            $info['agent_status'] = $row['agent_status'] ?? null;
            $info['tracked'] = $row['tracked'] ?? null;
        }

        match ($type) {
            'fe' => $app_data['frontends'][$pxname] = $info,
            'be' => $app_data['backends'][$pxname] = $info,
            'srv' => $app_data['servers'][$pxname][$svname] = $info,
            'li' => $app_data['listeners'][$pxname][$svname] = $info,
        };
    }
}

// totals... frontend values are preferred, falling back to backend
// values for the ones only backends have, such as econ, eresp, and wretr
// only done for groups that had at least one item
foreach ($sums as $group => $type_sums) {
    $total_fields = [];
    foreach ($groups[$group]['fields'] as [$ds, $ds_type]) {
        if (! in_array($ds, $no_total)) {
            $total_fields[$ds] = $type_sums['fe'][$ds] ?? $type_sums['be'][$ds] ?? null;
        }
    }

    $tags['rrd_def'] = $total_rrd_defs[$group];
    $tags['rrd_name'] = ['app', $name, $app->app_id, 'totals_-_' . $group];
    app('Datastore')->put($device, 'app', $tags, $total_fields);

    foreach ($total_fields as $ds => $value) {
        if (! is_null($value)) {
            $metrics['totals_' . $ds] = $value;
        }
    }
}

// counts of frontends/backends/servers/listeners by state, handy for alerting
$tags['rrd_def'] = RrdDefinition::make();
foreach ($counts as $ds => $value) {
    $tags['rrd_def']->addDataset($ds, 'GAUGE', 0);
}
$tags['rrd_name'] = ['app', $name, $app->app_id, 'counts'];
app('Datastore')->put($device, 'app', $tags, $counts);

foreach ($counts as $ds => $value) {
    $metrics['count_' . $ds] = $value;
}

$app->data = $app_data;
update_application($app, 'OK', $metrics);
