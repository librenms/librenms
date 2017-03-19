<?php

use LibreNMS\RRD\RrdDefinition;

echo "fail2ban";

$name = 'fail2ban';
$app_id = $app['app_id'];

$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutputFull.8.102.97.105.108.50.98.97.110';
$f2b = snmp_walk($device, $oid, $options, $mib);
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
    list( $jail, $banned )=explode(" ", $bannedStuff[$int]);

    $jails[]=$jail;

    if (isset($jail) && isset($banned)) {
        $rrd_name = array('app', $name, $app_id, $jail);
        $rrd_def = RrdDefinition::make()->addDataset('banned', 'GAUGE', 0);
        $fields = array('banned' =>$banned);

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
        'device_id' => array('=', $device_id),
        'type' => array('=', 'fail2ban'),
    ),
);

$component=new LibreNMS\Component();
$f2bc=$component->getComponents($device_id, $options);

// get or create a new fail2ban component and update it
if (isset($f2bc[$device_id])) {
    $f2bcs=array_keys($f2bc[$device_id]);

    //we should only ever have one of these, remove any extras
    $f2bcs_int=1;
    while (isset($f2bcs[$f2bcs_int])) {
        echo 'found extra fail2ban type component, removing component ID "'.$f2bcs[$f2bcs_int].'"'."\n";
        $component->deleteComponent($f2bcs[$f2bcs_int]);
        $f2bcs_int++;
    }

    $f2bc=$f2bc[$device_id][$f2bcs[0]];
    $f2bc['jails']=implode('|', $jails);
    $f2bc=array( $f2bcs[0] => $f2bc );
} else {
    $f2b=$component->createComponent($device_id,'fail2ban');
    $f2bcs=array_keys($f2b[$device_id]);
    $f2bc=$f2bc[$device_id][$f2bcs[0]];
    $f2bc['label']='fail2ban';
    $f2bc['jails']=implode('|', $jails);
    $f2bc=array( $f2bcs[0] => $f2bc );
}
$component->setComponentPrefs($device_id, $f2bc);
