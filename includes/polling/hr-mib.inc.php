<?php

// HOST-RESOURCES-MIB
// Generic System Statistics

use App\Models\HrSystem;
use LibreNMS\RRD\RrdDefinition;

$oid_list = ['hrSystemMaxProcesses.0', 'hrSystemProcesses.0', 'hrSystemNumUsers.0'];
$hrSystem = snmp_get_multi($device, $oid_list, '-OUQs', 'HOST-RESOURCES-MIB');

$hrSystemProcesses = $hrSystem[0]['hrSystemProcesses'] ?? null;
$hrSystemNumUsers = $hrSystem[0]['hrSystemNumUsers'] ?? null;
$hrSystemMaxProcesses = $hrSystem[0]['hrSystemMaxProcesses'] ?? null;

if (is_numeric($hrSystemProcesses)) {
    $tags = [
        'rrd_def' => RrdDefinition::make()->addDataset('procs', 'GAUGE', 0),
    ];
    $fields = [
        'procs' => $hrSystemProcesses,
    ];

    data_update($device, 'hr_processes', $tags, $fields);

    $os->enableGraph('hr_processes');
    echo ' Processes';
}

if (is_numeric($hrSystemNumUsers)) {
    $tags = [
        'rrd_def' => RrdDefinition::make()->addDataset('users', 'GAUGE', 0),
    ];
    $fields = [
        'users' => $hrSystemNumUsers,
    ];

    data_update($device, 'hr_users', $tags, $fields);

    HrSystem::updateOrCreate(['device_id' => $device['device_id']], [
        'hrSystemNumUsers' => $hrSystemNumUsers,
        'hrSystemProcesses' => $hrSystemProcesses,
        'hrSystemMaxProcesses' => $hrSystemMaxProcesses,
    ]);

    $os->enableGraph('hr_users');
    echo ' Users';
}

echo "\n";

unset($oid_list, $hrSystem, $hrSystemProcesses, $hrSystemMaxProcesses, $hrSystemNumUsers);
