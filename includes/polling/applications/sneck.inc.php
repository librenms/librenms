<?php

use Carbon\Carbon;
use LibreNMS\Config;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'sneck';
$app_id = $app['app_id'];

if (Config::has('apps.sneck.polling_time_diff')) {
    $compute_time_diff=Config::get('apps.sneck.polling_time_diff');
 } else {
    $compute_time_diff=false;
 }

try {
    $json_return = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    // Set empty metrics and error message
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []);

    return;
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
    $time_to_polling=0;
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

// update it here as we are done with this mostly
update_application($app, 'OK', $fields);

// all good, so return
if ($json_return['data']['warning'] == 0 &&
    $json_return['data']['critical'] == 0 &&
    $json_return['data']['unknown'] == 0 &&
    $json_return['data']['errored'] == 0 &&
    $json_return['data']['alert'] == 0 &&
    abs($time_to_polling) < 540) {
    return;
}

// remove this so if we have a alert, we don't spam the logs
unset($json_return['data']['time']);

if (abs($time_to_polling) > 540) {
    $json_return['data']['alertString'] = $json_return['data']['alertString'] . "\nGreater than 540 seconds since the polled data was generated";
    $json_return['data']['alert'] = 1;
}

//
// component processing for sneck for incase if some one is interested as to why something errored
//
$device_id = $device['device_id'];
$options = [
    'filter' => [
        'device_id' => ['=', $device_id],
        'type' => ['=', $name],
    ],
];

$component = new LibreNMS\Component();
$components = $component->getComponents($device_id, $options);

if (isset($components[$device_id])) {
    $ourc = $components[$device_id];
} else {
    $ourc = $component->createComponent($device_id, $name);
}

$id = $component->getFirstComponentID($ourc);
$ourc[$id]['label'] = $name;
$ourc[$id]['returned'] = json_encode($json_return);

$component->setComponentPrefs($device_id, $ourc);
