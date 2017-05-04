<?php

use LibreNMS\RRD\RrdDefinition;

echo "fail2ban";

$name = 'fail2ban';
$app_id = $app['app_id'];

$options      = '-O qv';
$oid          = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.8.102.97.105.108.50.98.97.110';
$f2b = snmp_walk($device, $oid, $options);
$f2b = trim($f2b, '"');
update_application($app, $f2b);

$bannedStuff = explode("\n", $f2b);

$total_banned=$bannedStuff[0];
$firewalled=$bannedStuff[1];

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('banned', 'GAUGE', 0)
    ->addDataset('firewalled', 'GAUGE', 0);

$fields = array(
    'banned' =>$total_banned,
    'firewalled' => $firewalled,
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);

$int=2;
$jails=array();

while (isset($bannedStuff[$int])) {
    list($jail, $banned) = explode(" ", $bannedStuff[$int]);

    if (isset($jail) && isset($banned)) {
        $jails[] = $jail;

        $rrd_name = array('app', $name, $app_id, $jail);
        $rrd_def = RrdDefinition::make()->addDataset('banned', 'GAUGE', 0);
        $fields = array('banned' => $banned);

        $tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
        data_update($device, 'app', $tags, $fields);
    }

    $int++;
}

//
// component processing for fail2ban
//
$device_id=$device['device_id'];

$options=array(
    'filter' => array(
        'type' => array('=', 'fail2ban'),
    ),
);

$component = new LibreNMS\Component();
$f2b_components = $component->getComponents($device_id, $options);

// if no jails, delete fail2ban components
if (empty($jails)) {
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
    $f2bc[$id]['jails'] = json_encode($jails);

    $component->setComponentPrefs($device_id, $f2bc);
}
