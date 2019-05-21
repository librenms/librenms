<?php

// HOST-RESOURCES-MIB
// Generic System Statistics
use LibreNMS\RRD\RrdDefinition;

$oid_list = ['hrSystemProcesses.0', 'hrSystemNumUsers.0'];
$hrSystem = snmp_get_multi($device, $oid_list, '-OUQs', 'HOST-RESOURCES-MIB');

if (is_numeric($hrSystem[0]['hrSystemProcesses'])) {
    $tags = array(
        'rrd_def' => RrdDefinition::make()->addDataset('procs', 'GAUGE', 0),
    );
    $fields = array(
        'procs' => $hrSystem[0]['hrSystemProcesses'],
    );

    data_update($device, 'hr_processes', $tags, $fields);

    $graphs['hr_processes'] = true;
    echo ' Processes';
}

if (is_numeric($hrSystem[0]['hrSystemNumUsers'])) {
    $tags = array(
        'rrd_def' => RrdDefinition::make()->addDataset('users', 'GAUGE', 0)
    );
    $fields = array(
        'users' => $hrSystem[0]['hrSystemNumUsers'],
    );

    data_update($device, 'hr_users', $tags, $fields);

    $graphs['hr_users'] = true;
    echo ' Users';
}

echo "\n";

unset($oid_list, $hrSystem);
