<?php

use LibreNMS\RRD\RrdDefinition;

$version = preg_replace('/(.+)\ version\ (.+)\ \(SN:\ (.+)\,\ (.+)\)/', '\\1||\\2||\\3||\\4', $device['sysDescr']);
list($hardware,$version,$serial,$features) = explode('||', $version);

$oids = array(
    '.1.3.6.1.4.1.3224.16.3.2.0',
    '.1.3.6.1.4.1.3224.16.3.3.0',
    '.1.3.6.1.4.1.3224.16.3.4.0',
);
$sess_data = snmp_get_multi_oid($device, $oids);
list ($sessalloc, $sessmax, $sessfailed) = array_values($sess_data);

$rrd_def = RrdDefinition::make()
    ->addDataset('allocate', 'GAUGE', 0, 3000000)
    ->addDataset('max', 'GAUGE', 0, 3000000)
    ->addDataset('failed', 'GAUGE', 0, 1000);

$fields = array(
    'allocate'  => $sessalloc,
    'max'       => $sessmax,
    'failed'    => $sessfailed,
);

$tags = compact('rrd_def');
data_update($device, 'screenos_sessions', $tags, $fields);

$graphs['screenos_sessions'] = true;
