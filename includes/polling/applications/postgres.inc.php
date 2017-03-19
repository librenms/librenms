<?php
$name = 'postgres';
$app_id = $app['app_id'];

use LibreNMS\RRD\RrdDefinition;

echo "postgres";

$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutputFull.8.112.111.115.116.103.114.101.115';
$postgres = snmp_walk($device, $oid, $options, $mib);
update_application($app, $postgres);

list($backends, $commits, $rollbacks, $read, $hit, $idxscan, $idxtupread, $idxtupfetch, $idxblksread,
    $idxblkshit, $seqscan, $seqtupread, $ret, $fetch, $ins, $upd, $del) = explode("\n", $postgres);

$rrd_name = array('app', $name, $app_id);

$rrd_def = RrdDefinition::make()
    ->addDataset('backends', 'GAUGE', 0)
    ->addDataset('commits', 'DERIVE', 0)
    ->addDataset('rollbacks', 'DERIVE', 0)
    ->addDataset('read', 'DERIVE', 0)
    ->addDataset('hit', 'DERIVE', 0)
    ->addDataset('idxscan', 'DERIVE', 0)
    ->addDataset('idxtupread', 'DERIVE', 0)
    ->addDataset('idxtupfetch', 'DERIVE', 0)
    ->addDataset('idxblksread', 'DERIVE', 0)
    ->addDataset('idxblkshit', 'DERIVE', 0)
    ->addDataset('seqscan', 'DERIVE', 0)
    ->addDataset('seqtupread', 'DERIVE', 0)
    ->addDataset('ret', 'DERIVE', 0)
    ->addDataset('fetch', 'DERIVE', 0)
    ->addDataset('ins', 'DERIVE', 0)
    ->addDataset('upd', 'DERIVE', 0)
    ->addDataset('del', 'DERIVE', 0);

$fields = array(
    'backends' => $backends,
    'commits' => $commits,
    'rollbacks' => $rollbacks,
    'read' => $read,
    'hit' => $hit,
    'idxscan' => $idxscan,
    'idxtupread' => $idxtupread,
    'idxtupfetch' => $idxtupfetch,
    'idxblksread' => $idxblksread,
    'idxblkshit' => $idxblkshit,
    'seqscan' => $seqscan,
    'seqtupread' => $seqtupread,
    'ret' => $ret,
    'fetch' => $fetch,
    'ins' => $ins,
    'upd' => $upd,
    'del' => $del
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);

//
//process each database
//
$db_lines=explode("\n", $postgres);
$db_lines_int=17;
$found_dbs=array();

while (isset($db_lines[$db_lines_int])) {
    list($backends, $commits, $rollbacks, $read, $hit, $idxscan, $idxtupread, $idxtupfetch, $idxblksread,
        $idxblkshit, $seqscan, $seqtupread, $ret, $fetch, $ins, $upd, $del, $dbname) = explode(" ", $db_lines[$db_lines_int]);

    $rrd_name = array('app', $name, $app_id, $dbname);

    $found_dbs[]=$dbname;

    $fields = array(
        'backends' => $backends,
        'commits' => $commits,
        'rollbacks' => $rollbacks,
        'read' => $read,
        'hit' => $hit,
        'idxscan' => $idxscan,
        'idxtupread' => $idxtupread,
        'idxtupfetch' => $idxtupfetch,
        'idxblksread' => $idxblksread,
        'idxblkshit' => $idxblkshit,
        'seqscan' => $seqscan,
        'seqtupread' => $seqtupread,
        'ret' => $ret,
        'fetch' => $fetch,
        'ins' => $ins,
        'upd' => $upd,
        'del' => $del
    );

    $tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
    data_update($device, 'app', $tags, $fields);

    $db_lines_int++;
}

//
// component processing for postgres
//
$device_id=$device['device_id'];

$options=array(
    'filter' => array( 
        'device_id' => array('=', $device_id),
        'type' => array('=', 'postgres'),
    ),
);

$component=new LibreNMS\Component();
$pgc=$component->getComponents($device_id, $options);

// get or create a new fail2ban component and update it
if (isset($pgc[$device_id])) {
    $pgcs=array_keys($pgc[$device_id]);

    //we should only ever have one of these, remove any extras
    $pgcs_int=1;
    while (isset($pgcs[$pgs_int])) {
        echo 'found extra postgres type component, removing component ID "'.$pgcs[$pgcs_int].'"'."\n";
        $component->deleteComponent($pgcs[$pgcs_int]);
        $pgcs_int++;
    }

    $pgc=$pgc[$device_id][$pgcs[0]];
    $pgc['databases']=implode('|', $found_dbs);
    $pgc=array( $pgcs[0] => $pgc );
} else {
    $pgc=$component->createComponent($device_id,'postgres');
    $pgcs=array_keys($pgc[$device_id]);
    $pgc=$pgc[$device_id][$pgcs[0]];
    $pgc['label']='postgres';
    $pgc['databases']=implode('|', $found_dbs);
    $pgc=array( $pgs[0] => $pgc );
}
$component->setComponentPrefs($device_id, $pgc);
