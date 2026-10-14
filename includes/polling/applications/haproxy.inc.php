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

// CSV column => [DS name, DS type]
// DS names are limited to 19 chars, so the longer ones are shortened.
$fields = [
    // counters
    'bin' => ['bin', 'DERIVE'],
    'bout' => ['bout', 'DERIVE'],
    'stot' => ['stot', 'DERIVE'],
    'req_tot' => ['req_tot', 'DERIVE'],
    'conn_tot' => ['conn_tot', 'DERIVE'],
    'lbtot' => ['lbtot', 'DERIVE'],
    'connect' => ['connect', 'DERIVE'],
    'reuse' => ['reuse', 'DERIVE'],
    'hrsp_1xx' => ['hrsp_1xx', 'DERIVE'],
    'hrsp_2xx' => ['hrsp_2xx', 'DERIVE'],
    'hrsp_3xx' => ['hrsp_3xx', 'DERIVE'],
    'hrsp_4xx' => ['hrsp_4xx', 'DERIVE'],
    'hrsp_5xx' => ['hrsp_5xx', 'DERIVE'],
    'hrsp_other' => ['hrsp_other', 'DERIVE'],
    'ereq' => ['ereq', 'DERIVE'],
    'econ' => ['econ', 'DERIVE'],
    'eresp' => ['eresp', 'DERIVE'],
    'eint' => ['eint', 'DERIVE'],
    'wrew' => ['wrew', 'DERIVE'],
    'dreq' => ['dreq', 'DERIVE'],
    'dresp' => ['dresp', 'DERIVE'],
    'dcon' => ['dcon', 'DERIVE'],
    'dses' => ['dses', 'DERIVE'],
    'intercepted' => ['intercepted', 'DERIVE'],
    'wretr' => ['wretr', 'DERIVE'],
    'wredis' => ['wredis', 'DERIVE'],
    'cli_abrt' => ['cli_abrt', 'DERIVE'],
    'srv_abrt' => ['srv_abrt', 'DERIVE'],
    'chkfail' => ['chkfail', 'DERIVE'],
    'chkdown' => ['chkdown', 'DERIVE'],
    'downtime' => ['downtime', 'DERIVE'],
    'ssl_sess' => ['ssl_sess', 'DERIVE'],
    'ssl_reused_sess' => ['ssl_reused_sess', 'DERIVE'],
    'ssl_failed_handshake' => ['ssl_failed_hs', 'DERIVE'],
    'comp_in' => ['comp_in', 'DERIVE'],
    'comp_out' => ['comp_out', 'DERIVE'],
    'comp_byp' => ['comp_byp', 'DERIVE'],
    'comp_rsp' => ['comp_rsp', 'DERIVE'],
    'cache_lookups' => ['cache_lookups', 'DERIVE'],
    'cache_hits' => ['cache_hits', 'DERIVE'],
    'h1_bytes_in' => ['h1_bytes_in', 'DERIVE'],
    'h1_bytes_out' => ['h1_bytes_out', 'DERIVE'],
    'h1_spliced_bytes_in' => ['h1_splice_bytes_in', 'DERIVE'],
    'h1_spliced_bytes_out' => ['h1_splice_bytes_out', 'DERIVE'],
    'h1_total_connections' => ['h1_tot_conn', 'DERIVE'],
    'h1_total_streams' => ['h1_tot_streams', 'DERIVE'],
    'h2_total_connections' => ['h2_tot_conn', 'DERIVE'],
    'h2_backend_total_streams' => ['h2_be_tot_streams', 'DERIVE'],
    'h2_headers_rcvd' => ['h2_headers_rcvd', 'DERIVE'],
    'h2_data_rcvd' => ['h2_data_rcvd', 'DERIVE'],
    'h2_settings_rcvd' => ['h2_settings_rcvd', 'DERIVE'],
    'h2_rst_stream_rcvd' => ['h2_rst_stream_rcvd', 'DERIVE'],
    'h2_goaway_rcvd' => ['h2_goaway_rcvd', 'DERIVE'],
    'h2_rst_stream_resp' => ['h2_rst_stream_resp', 'DERIVE'],
    'h2_goaway_resp' => ['h2_goaway_resp', 'DERIVE'],
    'h2_detected_conn_protocol_errors' => ['h2_conn_proto_err', 'DERIVE'],
    'h2_detected_strm_protocol_errors' => ['h2_strm_proto_err', 'DERIVE'],
    // gauges
    'scur' => ['scur', 'GAUGE'],
    'qcur' => ['qcur', 'GAUGE'],
    'rate' => ['rate', 'GAUGE'],
    'conn_rate' => ['conn_rate', 'GAUGE'],
    'req_rate' => ['req_rate', 'GAUGE'],
    'qtime' => ['qtime', 'GAUGE'],
    'ctime' => ['ctime', 'GAUGE'],
    'rtime' => ['rtime', 'GAUGE'],
    'ttime' => ['ttime', 'GAUGE'],
    'check_duration' => ['check_duration', 'GAUGE'],
    'weight' => ['weight', 'GAUGE'],
    'uweight' => ['uweight', 'GAUGE'],
    'act' => ['act', 'GAUGE'],
    'bck' => ['bck', 'GAUGE'],
    'throttle' => ['throttle', 'GAUGE'],
    'idle_conn_cur' => ['idle_conn_cur', 'GAUGE'],
    'used_conn_cur' => ['used_conn_cur', 'GAUGE'],
    'safe_conn_cur' => ['safe_conn_cur', 'GAUGE'],
    'need_conn_est' => ['need_conn_est', 'GAUGE'],
    'srv_icur' => ['srv_icur', 'GAUGE'],
    'h1_open_connections' => ['h1_open_conn', 'GAUGE'],
    'h1_open_streams' => ['h1_open_streams', 'GAUGE'],
    'h2_open_connections' => ['h2_open_conn', 'GAUGE'],
    'h2_backend_open_streams' => ['h2_be_open_streams', 'GAUGE'],
];

// fields that do not make sense to sum for the totals
$no_total = ['qtime', 'ctime', 'rtime', 'ttime', 'check_duration', 'weight', 'uweight', 'throttle'];

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

$rrd_def = RrdDefinition::make()->addDataset('status', 'GAUGE', 0);
foreach ($fields as $field => [$ds, $type]) {
    $rrd_def->addDataset($ds, $type, 0);
}

$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_def' => $rrd_def,
];

$metrics = [];
$app_data = [
    'frontends' => [],
    'backends' => [],
    'servers' => [],
    'listeners' => [],
];

$counts = [];
foreach (['fe', 'be', 'srv', 'li'] as $type) {
    foreach (['total', 'up', 'down', 'maint', 'drain', 'nolb', 'other'] as $state) {
        $counts[$type . '_' . $state] = 0;
    }
}

// sums used for the totals, done per type and picked from after
$sums = ['fe' => [], 'be' => []];

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

        $rrd_fields = ['status' => $status];
        foreach ($fields as $field => [$ds, $ds_type]) {
            $rrd_fields[$ds] = $row[$field] ?? null;
            if (isset($sums[$type]) && ! in_array($ds, $no_total) && isset($row[$field]) && is_numeric($row[$field])) {
                $sums[$type][$ds] = ($sums[$type][$ds] ?? 0) + $row[$field];
            }
        }

        if ($type == 'fe' || $type == 'be') {
            $id = $pxname;
            $tags['rrd_name'] = ['app', $name, $app->app_id, $type . '_-_' . $pxname];
        } else {
            $id = $pxname . '_-_' . $svname;
            $tags['rrd_name'] = ['app', $name, $app->app_id, $type . '_-_' . $pxname . '_-_' . $svname];
        }

        app('Datastore')->put($device, 'app', $tags, $rrd_fields);

        // the metric column is limited to 64 chars, so skip any that would be too long
        foreach ($rrd_fields as $ds => $value) {
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
            $info['act'] = $row['act'] ?? null;
            $info['bck'] = $row['bck'] ?? null;
            $info['weight'] = $row['weight'] ?? null;
        }
        if ($type == 'srv') {
            $info['addr'] = $row['addr'] ?? null;
            $info['check_status'] = $row['check_status'] ?? null;
            $info['check_desc'] = $row['check_desc'] ?? null;
            $info['check_code'] = $row['check_code'] ?? null;
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
$total_fields = [];
foreach ($fields as [$ds, $ds_type]) {
    if (in_array($ds, $no_total)) {
        continue;
    }
    $total_fields[$ds] = $sums['fe'][$ds] ?? $sums['be'][$ds] ?? null;
}
$total_rrd_def = RrdDefinition::make();
foreach ($fields as [$ds, $ds_type]) {
    if (! in_array($ds, $no_total)) {
        $total_rrd_def->addDataset($ds, $ds_type, 0);
    }
}
$tags['rrd_def'] = $total_rrd_def;
$tags['rrd_name'] = ['app', $name, $app->app_id, 'totals'];
app('Datastore')->put($device, 'app', $tags, $total_fields);

foreach ($total_fields as $ds => $value) {
    if (! is_null($value)) {
        $metrics['totals_' . $ds] = $value;
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
