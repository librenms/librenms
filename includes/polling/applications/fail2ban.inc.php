<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'fail2ban';
$app_id = $app['app_id'];

echo $name;

try {
    $f2b = json_app_get($device, $name);
} catch (JsonAppParsingFailedException $e) {
    // Legacy script, build compatible array
    $legacy = explode("\n", $e->getOutput());
    $f2b = [
        'data' => [
            'total' => array_shift($legacy), // total was first line in legacy app
            'jails' => [],
        ],
    ];

    foreach ($legacy as $jail_data) {
        [$jail, $banned] = explode(' ', $jail_data);
        if (isset($jail) && isset($banned)) {
            $f2b['data']['jails'][$jail] = $banned;
        }
    }
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$f2b = $f2b['data'];

$metrics = [];

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('banned', 'GAUGE', 0)
    ->addDataset('firewalled', 'GAUGE', 0);

$fields = ['banned' => $f2b['total']];
$metrics['total'] = $fields; // don't include legacy ds in db
$fields['firewalled'] = 'U'; // legacy ds

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

foreach ($f2b['jails'] as $jail => $banned) {
    $rrd_name = ['app', $name, $app_id, $jail];
    $rrd_def = RrdDefinition::make()->addDataset('banned', 'GAUGE', 0);
    $fields = ['banned' => $banned];

    $metrics["jail_$jail"] = $fields;
    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

update_application($app, 'ok', $metrics);

//
// component processing for fail2ban
//
$device_id = $device['device_id'];

$options = [
    'filter' => [
        'type' => ['=', 'fail2ban'],
    ],
];

$component = new LibreNMS\Component();
$f2b_components = $component->getComponents($device_id, $options);

// if no jails, delete fail2ban components
if (empty($f2b['jails'])) {
    if (isset($f2b_components[$device_id])) {
        foreach ($f2b_components[$device_id] as $component_id => $_unused) {
            $component->deleteComponent($component_id);
        }
    }
} else {
    if (isset($f2b_components[$device_id])) {
        $f2bc = $f2b_components[$device_id];
    } else {
        $f2bc = $component->createComponent($device_id, 'fail2ban');
    }

    $id = $component->getFirstComponentID($f2bc);
    $f2bc[$id]['label'] = 'Fail2ban Jails';
    $jails = array_keys($f2b['jails']);
    sort($jails);
    $f2bc[$id]['jails'] = json_encode(array_values($jails));

    $component->setComponentPrefs($device_id, $f2bc);
}
