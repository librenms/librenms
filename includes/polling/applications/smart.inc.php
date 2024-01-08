<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'smart';

try {
    $data = json_app_get($device, $name)['data'];
} catch (JsonAppParsingFailedException $e) {
    // Legacy script, build compatible array
    $legacy = $e->getOutput();
    $lines = explode("\n", $legacy);

    $data = ['disks' => [], 'legacy' => 1];

    $int = 0;
    while (isset($lines[$int])) {
        [$disk, $id5, $id10, $id173, $id177, $id183, $id184, $id187, $id188, $id190, $id194,
            $id196, $id197, $id198, $id199, $id231, $id233, $completed, $interrupted, $read_failure,
            $unknown_failure, $extended, $short, $conveyance, $selective, $id9] = explode(',', $lines[$int]);
        $int++;

        // could really be any of these, but make sure we have something defined,
        // otherwise there is something wrong with the line
        if (isset($id173)) {
            $data['disks'][$disk] = [
                '10' => $id10,
                '173' => $id173,
                '177' => $id177,
                '183' => $id183,
                '184' => $id184,
                '187' => $id187,
                '188' => $id188,
                '190' => $id190,
                '194' => $id194,
                '196' => $id196,
                '197' => $id197,
                '198' => $id198,
                '199' => $id199,
                '231' => $id231,
                '233' => $id233,
                '5' => $id5,
                '9' => $id9,
                'completed' => $completed,
                'interrupted' => $interrupted,
                'read_failure' => $read_failure,
                'unknown_failure' => $unknown_failure,
                'extended' => $extended,
                'short' => $short,
                'conveyance' => $conveyance,
                'selective' => 'selective',
            ];
        }
    }
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$old_data = $app->data;
if (! isset($old_data['disks_with_failed_tests'])) {
    $old_data['disks_with_failed_tests'] = [];
}
if (! isset($old_data['disks_with_failed_health'])) {
    $old_data['disks_with_failed_health'] = [];
}

$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('id5', 'GAUGE', 0)
    ->addDataset('id10', 'GAUGE', 0)
    ->addDataset('id173', 'GAUGE', 0)
    ->addDataset('id177', 'GAUGE', 0)
    ->addDataset('id183', 'GAUGE', 0)
    ->addDataset('id184', 'GAUGE', 0)
    ->addDataset('id187', 'GAUGE', 0)
    ->addDataset('id188', 'GAUGE', 0)
    ->addDataset('id190', 'GAUGE', 0)
    ->addDataset('id194', 'GAUGE', 0)
    ->addDataset('id196', 'GAUGE', 0)
    ->addDataset('id197', 'GAUGE', 0)
    ->addDataset('id198', 'GAUGE', 0)
    ->addDataset('id199', 'GAUGE', 0)
    ->addDataset('id231', 'GAUGE', 0)
    ->addDataset('id233', 'GAUGE', 0)
    ->addDataset('completed', 'GAUGE', 0)
    ->addDataset('interrupted', 'GAUGE', 0)
    ->addDataset('readfailure', 'GAUGE', 0)
    ->addDataset('unknownfail', 'GAUGE', 0)
    ->addDataset('extended', 'GAUGE', 0)
    ->addDataset('short', 'GAUGE', 0)
    ->addDataset('conveyance', 'GAUGE', 0)
    ->addDataset('selective', 'GAUGE', 0);

$rrd_def_id9 = RrdDefinition::make()
    ->addDataset('id9', 'GAUGE', 0);

$rrd_def_id232 = RrdDefinition::make()
    ->addDataset('id232', 'GAUGE', 0);

$rrd_def_maxtemp = RrdDefinition::make()
    ->addDataset('maxtemp', 'GAUGE', 0);

$new_disks_with_failed_tests = [];
$new_disks_with_failed_health = [];
$data['disks_with_failed_tests'] = [];
$data['disks_with_failed_health'] = [];
$data['has'] = [
    'id5' => 0,
    'id9' => 0,
    'id10' => 0,
    'id173' => 0,
    'id177' => 0,
    'id183' => 0,
    'id184' => 0,
    'id187' => 0,
    'id188' => 0,
    'id190' => 0,
    'id194' => 0,
    'id196' => 0,
    'id197' => 0,
    'id198' => 0,
    'id199' => 0,
    'id231' => 0,
    'id232' => 0,
    'id233' => 0,
];

$metrics = [
    'disks_with_failed_tests_count' => 0,
    'disks_with_failed_health_count' => 0,
    'new_disks_with_failed_tests_count' => 0,
    'new_disks_with_failed_health_count' => 0,
    'exit_nonzero' => $data['exit_nonzero'],
    'unhealthy' => $data['unhealthy'],
];
foreach ($data['disks'] as $disk_id => $disk) {
    $rrd_name = ['app', $name, $app->app_id, $disk_id];

    $fields = [
        'id5' => is_numeric($disk['5']) ? $disk['5'] : null,
        'id10' => is_numeric($disk['10']) ? $disk['10'] : null,
        'id173' => is_numeric($disk['173']) ? $disk['173'] : null,
        'id177' => is_numeric($disk['177']) ? $disk['177'] : null,
        'id183' => is_numeric($disk['183']) ? $disk['183'] : null,
        'id184' => is_numeric($disk['184']) ? $disk['184'] : null,
        'id187' => is_numeric($disk['187']) ? $disk['187'] : null,
        'id188' => is_numeric($disk['188']) ? $disk['188'] : null,
        'id190' => is_numeric($disk['190']) ? $disk['190'] : null,
        'id194' => is_numeric($disk['194']) ? $disk['194'] : null,
        'id196' => is_numeric($disk['196']) ? $disk['196'] : null,
        'id197' => is_numeric($disk['197']) ? $disk['197'] : null,
        'id198' => is_numeric($disk['198']) ? $disk['198'] : null,
        'id199' => is_numeric($disk['199']) ? $disk['199'] : null,
        'id231' => is_numeric($disk['231']) ? $disk['231'] : null,
        'id233' => is_numeric($disk['233']) ? $disk['233'] : null,
        'completed' => is_numeric($disk['completed']) ? $disk['completed'] : null,
        'interrupted' => is_numeric($disk['interrupted']) ? $disk['interrupted'] : null,
        'readfailure' => is_numeric($disk['read_failure']) ? $disk['read_failure'] : null,
        'unknownfail' => is_numeric($disk['unknown_failure']) ? $disk['unknown_failure'] : null,
        'extended' => is_numeric($disk['extended']) ? $disk['extended'] : null,
        'short' => is_numeric($disk['short']) ? $disk['short'] : null,
        'conveyance' => is_numeric($disk['conveyance']) ? $disk['conveyance'] : null,
        'selective' => is_numeric($disk['selective']) ? $disk['selective'] : null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    $metrics['disk_' . $disk_id . '_id5'] = $fields['id5'];
    $metrics['disk_' . $disk_id . '_id10'] = $fields['id10'];
    $metrics['disk_' . $disk_id . '_id173'] = $fields['id173'];
    $metrics['disk_' . $disk_id . '_id177'] = $fields['id177'];
    $metrics['disk_' . $disk_id . '_id183'] = $fields['id183'];
    $metrics['disk_' . $disk_id . '_id184'] = $fields['id184'];
    $metrics['disk_' . $disk_id . '_id187'] = $fields['id187'];
    $metrics['disk_' . $disk_id . '_id188'] = $fields['id188'];
    $metrics['disk_' . $disk_id . '_id190'] = $fields['id190'];
    $metrics['disk_' . $disk_id . '_id194'] = $fields['id194'];
    $metrics['disk_' . $disk_id . '_id196'] = $fields['id196'];
    $metrics['disk_' . $disk_id . '_id197'] = $fields['id197'];
    $metrics['disk_' . $disk_id . '_id198'] = $fields['id198'];
    $metrics['disk_' . $disk_id . '_id199'] = $fields['id199'];
    $metrics['disk_' . $disk_id . '_id231'] = $fields['id231'];
    $metrics['disk_' . $disk_id . '_id233'] = $fields['id233'];
    $metrics['disk_' . $disk_id . '_completed'] = $fields['completed'];
    $metrics['disk_' . $disk_id . '_interrupted'] = $fields['interrupted'];
    $metrics['disk_' . $disk_id . '_readfailure'] = $fields['readfailure'];
    $metrics['disk_' . $disk_id . '_unknownfail'] = $fields['unknownfail'];
    $metrics['disk_' . $disk_id . '_extended'] = $fields['extended'];
    $metrics['disk_' . $disk_id . '_short'] = $fields['short'];
    $metrics['disk_' . $disk_id . '_conveyance'] = $fields['conveyance'];
    $metrics['disk_' . $disk_id . '_selective'] = $fields['selective'];

    $rrd_name_id9 = ['app', $name . '_id9', $app->app_id, $disk_id];
    $fields_id9 = ['id9' => $disk['9']];
    $tags_id9 = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_id9, 'rrd_name' => $rrd_name_id9];
    data_update($device, 'app', $tags_id9, $fields_id9);

    $metrics['disk_' . $disk_id . '_id9'] = $disk['9'];

    $rrd_name_id232 = ['app', $name . '_id232', $app->app_id, $disk_id];
    $fields_id232 = ['id232' => $disk['232']];
    $tags_id232 = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_id232, 'rrd_name' => $rrd_name_id232];
    data_update($device, 'app', $tags_id232, $fields_id232);

    $metrics['disk_' . $disk_id . '_id232'] = $disk['232'];

    $rrd_name_maxtemp = ['app', $name . '_maxtemp', $app->app_id, $disk_id];
    $fields_maxtemp = ['maxtemp' => $disk['max_temp']];
    $tags_maxtemp = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_maxtemp, 'rrd_name' => $rrd_name_maxtemp];
    data_update($device, 'app', $tags_maxtemp, $fields_maxtemp);

    $metrics['disk_' . $disk_id . '_max_temp'] = $disk['max_temp'];

    // check if it has any failed tests
    // only counting failures, ignoring ones that have been interrupted
    if ((is_numeric($disk['read_failure']) && $disk['read_failure'] > 0) ||
      (is_numeric($disk['unknown_failure']) && $disk['unknown_failure'] > 0)) {
        $data['disks_with_failed_tests'][$disk_id] = 1;
        $metrics['disks_with_failed_tests']++;
        // add it to the list to alert on if it is a new failure
        if (! isset($old_data['disks_with_failed_tests'])) {
            $new_disks_with_failed_tests[] = $disk_id;
            $metrics['new_disks_with_failed_tests']++;
        }
    }

    // check for what IDs we actually got
    foreach (['5', '9', '10', '173', '177', '183', '184', '187', '188', '190', '194', '196', '197', '198', '199', '231', '232', '233'] as $id_check) {
        if (is_numeric($disk[$id_check])) {
            $data['has']['id' . $id_check] = 1;
        }
    }

    // figure out if this disk is a SSD or not
    if (is_numeric($disk['173']) || is_numeric($disk['177']) || is_numeric($disk['231']) || is_numeric($disk['232']) || is_numeric($disk['233'])) {
        $data['disks'][$disk_id]['is_ssd'] = 1;
        $metrics['disk_' . $disk_id]['is_ssd'] = 1;
    } else {
        $data['disks'][$disk_id]['is_ssd'] = 0;
        $metrics['disk_' . $disk_id]['is_ssd'] = 0;
    }

    // checks if the health has failed
    if (isset($disk['health_pass']) && is_numeric($disk['health_pass']) && $disk['health_pass'] < 1) {
        $data['disks_with_failed_health'][$disk_id] = 1;
        $metrics['disks_with_failed_health_count']++;
        // add it to the list to alert on if it is a new failure
        if (! isset($old_data['disks_with_failed_health'])) {
            $new_disks_with_failed_health[] = $disk_id;
            $metrics['new_disks_with_failed_health_count']++;
        }
    }

    $metrics['disk_' . $disk_id . '_health'] = $disk['health_pass'];
    $metrics['disk_' . $disk_id . '_exit'] = $disk['exit'];
}

// log any disks with failed tests found
if (sizeof($new_disks_with_failed_tests) > 0) {
    $log_message = 'SMART found new disks with failed tests: ' . json_encode($new_disks_with_failed_tests);
    log_event($log_message, $device, 'application', 5);
}

// log when there when we go to having no failed disks from having them previously
if (sizeof($data['disks_with_failed_tests']) == 0 && sizeof($old_data['disks_with_failed_tests']) > 0) {
    $log_message = 'SMART is no longer finding any disks with failed tests';
    log_event($log_message, $device, 'application', 1);
}

// log any disks with failed tests found
if (sizeof($new_disks_with_failed_health) > 0) {
    $log_message = 'SMART found new disks with failed health checks: ' . json_encode($new_disks_with_failed_health);
    log_event($log_message, $device, 'application', 5);
}

// log when there when we go to having no failed disks from having them previously
if (sizeof($data['disks_with_failed_health']) == 0 && sizeof($old_data['disks_with_failed_health']) > 0) {
    $log_message = 'SMART is no longer finding any disks with failed health checks';
    log_event($log_message, $device, 'application', 1);
}

$app->data = $data;

update_application($app, 'OK', $metrics);
