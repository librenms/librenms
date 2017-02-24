<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'fail2ban';
$app_id = $app['app_id'];

$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutputFull.8.102.97.105.108.50.98.97.110';
$f2b = snmp_walk($device, $oid, $options, $mib);

$bannedStuff = explode("\n", $f2b);

$banned=$bannedStuff[0];
$firewalled=$bannedStuff[1];

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('banned', 'GAUGE', 0)
    ->addDataset('firewalled', 'GAUGE', 0);

$fields = array(
    'banned' =>$banned,
    'firewalled' => $firewalled,
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);

$int=2;
while (isset($bannedStuff[$int])) {
    list( $jail, $banned )=explode(" ", $bannedStuff[$int]);

    if (isset($jail) && isset($banned)) {
        $rrd_name = array('app', $name, $app_id, $jail);
        $rrd_def = RrdDefinition::make()->addDataset('banned', 'GAUGE', 0);
        $fields = array('banned' =>$banned);

        $tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
        data_update($device, 'app', $tags, $fields);
    }

    $int++;
}
