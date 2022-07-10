<?php

use Carbon\Carbon;
use LibreNMS\Config;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'sneck';
$app_id = $app['app_id'];

$old_checks = [];
if (isset($app_data['data']) and isset($app_data['data']['checks'])) {
    $old_checks = array_keys($app_data['data']['checks']);
}

if (Config::has('apps.sneck.polling_time_diff')) {
    $compute_time_diff = Config::get('apps.sneck.polling_time_diff');
} else {
    $compute_time_diff = false;
}

try {
    $json_return = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    // Set empty metrics and error message
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []);

    return;
}

$new_checks = [];
if (isset($json_return['data']) and isset($json_return['data']['checks'])) {
    $new_checks = array_keys($json_return['data']['checks']);
}

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('time', 'DERIVE', 0)
    ->addDataset('time_to_polling', 'GAUGE', 0)
    ->addDataset('ok', 'GAUGE', 0)
    ->addDataset('warning', 'GAUGE', 0)
    ->addDataset('critical', 'GAUGE', 0)
    ->addDataset('unknown', 'GAUGE', 0)
    ->addDataset('errored', 'GAUGE', 0);

// epoch off set between poller and when the when the JSON was generated
// only compueted if
if ($compute_time_diff) {
    $time_to_polling = Carbon::now()->timestamp - $json_return['data']['time'];
} else {
    $time_to_polling = 0;
}

$fields = [
    'time' => $json_return['data']['time'],
    'time_to_polling' => $time_to_polling,
    'ok' => $json_return['data']['ok'],
    'warning' => $json_return['data']['warning'],
    'critical' => $json_return['data']['critical'],
    'unknown' => $json_return['data']['unknown'],
    'errored' => $json_return['data']['errored'],
];

// save the return status for each alerting possibilities
foreach ($json_return['data']['checks'] as $key=>$value) {
    $fields['checks_' . $key] = $value['exit'];
}

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$fields['time_to_polling_abs'] = abs($time_to_polling);

if (abs($time_to_polling) > 540) {
    $json_return['data']['alertString'] = $json_return['data']['alertString'] . "\nGreater than 540 seconds since the polled data was generated";
    $json_return['data']['alert'] = 1;
}

//check for added checks
$added_checks = array_values(array_diff($new_checks, $old_checks));

//check for removed checks
$removed_checks = array_values(array_diff($old_checks, $new_checks));

// if we have any check changes, log it
if (sizeof($added_checks) > 0 or sizeof($removed_checks) > 0) {
    $log_message = 'Sneck Check Change:';
    $log_message .= count($added_checks) > 0 ? ' Added ' . json_encode($added_checks) : '';
    $log_message .= count($removed_checks) > 0 ? ' Removed ' . json_encode($added_checks) : '';
    log_event($log_message, $device, 'application');
}

// go through and looking for status changes
$cleared=[];
$warned=[];
$alerted=[];
$unknowned=[];
foreach ($new_checks as $check) {

}

// update it here as we are done with this mostly
update_application($app, 'OK', $fields);

// save the json_return to the app data
$app_data = $json_return;
