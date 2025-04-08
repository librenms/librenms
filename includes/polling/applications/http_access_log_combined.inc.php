<?php

use App\Models\Eventlog;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'http_access_log_combined';

try {
    $returned = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$stat_vars = [
    '100',
    '101',
    '102',
    '103',
    '1xx',
    '200',
    '201',
    '202',
    '203',
    '204',
    '205',
    '206',
    '207',
    '208',
    '218',
    '226',
    '2xx',
    '300',
    '301',
    '302',
    '303',
    '304',
    '305',
    '306',
    '307',
    '308',
    '3xx',
    '400',
    '401',
    '402',
    '403',
    '404',
    '405',
    '406',
    '407',
    '408',
    '409',
    '410',
    '411',
    '412',
    '413',
    '414',
    '415',
    '416',
    '417',
    '419',
    '420',
    '421',
    '422',
    '423',
    '424',
    '425',
    '426',
    '428',
    '429',
    '431',
    '444',
    '451',
    '494',
    '495',
    '496',
    '497',
    '499',
    '4xx',
    '500',
    '501',
    '502',
    '503',
    '504',
    '505',
    '506',
    '507',
    '508',
    '509',
    '510',
    '511',
    '5xx',
    'CONNECT',
    'DELETE',
    'GET',
    'HEAD',
    'OPTIONS',
    'PATCH',
    'POST',
    'PUT',
    'bytes',
    'bytes_max',
    'bytes_mean',
    'bytes_median',
    'bytes_min',
    'bytes_mode',
    'bytes_range',
    'error_size',
    'http1_0',
    'http1_1',
    'http2',
    'http3',
    'no_refer',
    'no_user',
    'refer',
    'size',
    'user',
];

$metrics = [];
$old_data = $app->data;
$new_data = [];

$data = $returned['data'];

$gauge_rrd_def = RrdDefinition::make()
    ->addDataset('data', 'GAUGE', 0);

// process total stats, .data.totals
foreach ($stat_vars as $key => $stat) {
    $var_name = 'totals_' . $stat;
    $value = $data['totals'][$stat];
    $rrd_name = ['app', $name, $app->app_id, $var_name];
    $fields = ['data' => $value];
    $metrics[$var_name] = $value;
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $gauge_rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

// process each log under .data.logs
$logs = [];
foreach ($data['logs'] as $logs_key => $log_stats) {
    $logs[] = $logs_key;
    foreach ($stat_vars as $key => $stat) {
        $var_name = 'logs___' . $logs_key . '___' . $stat;
        $value = $log_stats[$stat];
        $rrd_name = ['app', $name, $app->app_id, $var_name];
        $fields = ['data' => $value];
        $metrics[$var_name] = $value;
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $gauge_rrd_def, 'rrd_name' => $rrd_name];
        data_update($device, 'app', $tags, $fields);
    }
}

// check for added or removed logs
sort($logs);
$old_logs = $old_data['logs'] ?? [];
$added_logs = array_diff($logs, $old_logs);
$removed_logs = array_diff($old_logs, $logs);
$new_data['logs'] = $logs;

$app->data = $new_data;

// if we have any source instances, save and log
if (count($added_logs) > 0 || count($removed_logs) > 0) {
    $log_message = 'HTTP Access Log Set Change:';
    $log_message .= count($added_logs) > 0 ? ' Added ' . implode(',', $added_logs) : '';
    $log_message .= count($removed_logs) > 0 ? ' Removed ' . implode(',', $added_logs) : '';
    Eventlog::log($log_message, $device['device_id'], 'application');
}

// all done so update the app metrics
update_application($app, 'OK', $metrics);
