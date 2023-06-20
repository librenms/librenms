<?php

use Carbon\Carbon;
use LibreNMS\Config;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'sneck';

$old_checks = [];
$old_checks_data = [];
if (isset($app->data['data']) && isset($app->data['data']['checks'])) {
    $old_checks = array_keys($app->data['data']['checks']);
    $old_checks_data = $app->data['data']['checks'];
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

$app->data = $json_return;

$new_checks = [];
if (isset($json_return['data']) and isset($json_return['data']['checks'])) {
    $new_checks = array_keys($json_return['data']['checks']);
}

$rrd_name = ['app', $name, $app->app_id];
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

$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

// save the return status for each alerting possibilities
foreach ($json_return['data']['checks'] as $key => $value) {
    $fields['check_' . $key] = $value['exit'];
}

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
if (sizeof($added_checks) > 0 || sizeof($removed_checks) > 0) {
    $log_message = 'Sneck Check Change:';
    $log_message .= count($added_checks) > 0 ? ' Added ' . json_encode($added_checks) : '';
    $log_message .= count($removed_checks) > 0 ? ' Removed ' . json_encode($added_checks) : '';
    log_event($log_message, $device, 'application');
}

// go through and looking for status changes
$cleared = [];
$warned = [];
$alerted = [];
$unknowned = [];
foreach ($new_checks as $check) {
    if (isset($old_checks_data[$check]) && isset($old_checks_data[$check]['exit']) && isset($old_checks_data[$check]['output'])) {
        if ($json_return['data']['checks'][$check]['exit'] != $app->data['data']['checks'][$check]['exit']) {
            $check_output = $json_return['data']['checks'][$check]['output'];
            $exit_code = $json_return['data']['checks'][$check]['exit'];

            if ($exit_code == 1) {
                $warned[$check] = $check_output;
            } elseif ($exit_code == 2) {
                $alerted[$check] = $check_output;
            } elseif ($exit_code >= 3) {
                $unknowned[$check] = $check_output;
            } elseif ($exit_code == 0) {
                $cleared[$check] = $check_output;
            }
        }
    } else {
        if (isset($json_return['data']['checks'][$check]['exit']) && isset($json_return['data']['checks'][$check]['output'])) {
            $check_output = $json_return['data']['checks'][$check]['output'];
            $exit_code = $json_return['data']['checks'][$check]['exit'];

            if ($exit_code == 1) {
                $warned[$check] = $check_output;
            } elseif ($exit_code == 2) {
                $alerted[$check] = $check_output;
            } elseif ($exit_code >= 3) {
                $unknowned[$check] = $check_output;
            }
        }
    }
}

// log any clears
if (sizeof($cleared) > 0) {
    $log_message = 'Sneck Check Clears: ' . json_encode($cleared);
    log_event($log_message, $device, 'application', 1);
}

// log any warnings
if (sizeof($warned) > 0) {
    $log_message = 'Sneck Check Warns: ' . json_encode($warned);
    log_event($log_message, $device, 'application', 4);
}

// log any alerts
if (sizeof($alerted) > 0) {
    $log_message = 'Sneck Check Alerts: ' . json_encode($alerted);
    log_event($log_message, $device, 'application', 5);
}

// log any unknowns
if (sizeof($unknowned) > 0) {
    $log_message = 'Sneck Check Unknowns: ' . json_encode($unknownwed);
    log_event($log_message, $device, 'application', 6);
}

// update it here as we are done with this mostly
update_application($app, 'OK', $fields);
