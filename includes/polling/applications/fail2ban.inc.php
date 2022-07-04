<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'fail2ban';
$app_id = $app['app_id'];

$app_data = get_app_data($app_id);

if (! is_array($app_data['jails'])) {
    $app_data['jails'] = [];
}


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

$jails=[];
foreach ($f2b['jails'] as $jail => $banned) {
    $rrd_name = ['app', $name, $app_id, $jail];
    $rrd_def = RrdDefinition::make()->addDataset('banned', 'GAUGE', 0);
    $fields = ['banned' => $banned];

    $metrics["jail_$jail"] = $fields;
    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    $jails[]=$jail;
}
$old_jails = $app_data['jails'];


// save thge found jails
$app_data['jails'] = $jails;
save_app_data($app_id, $app_data);

//check for added jails
$added_jails = [];
foreach ($jails as $jail_check) {
    $jail_found = false;
    foreach ($old_jails as $jail_check2) {
        if ($jail_check == $jail_check2) {
            $jail_found = true;
        }
    }
    if (! $jail_found) {
        $added_jails[] = $jail_check;
    }
}

//check for removed jails
$removed_jails = [];
foreach ($old_jails as $jail_check) {
    $jail_found = false;
    foreach ($jails as $jail_check2) {
        if ($jail_check == $jail_check2) {
            $jail_found = true;
        }
    }
    if (! $jail_found) {
        $removed_jails[] = $jail_check;
    }
}

// if we have any jail changes, log it
if (isset($added_jails[0]) or isset($removed_jails[0])) {
    $log_message = 'Fail2ban Jail Change:';
    if (isset($added_jails[0])) {
        $log_message = $log_message . ' Added' . json_encode($added_jails);
    }
    if (isset($removed_jails[0])) {
        $log_message = $log_message . ' Removed' . json_encode($removed_jails);
    }
    log_event($log_message, $device, 'application');
}


update_application($app, 'ok', $metrics);

