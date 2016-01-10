<?php

// HOST-RESOURCES-MIB
// Generic System Statistics
$oid_list = 'hrSystemProcesses.0 hrSystemNumUsers.0';
$hrSystem = snmp_get_multi($device, $oid_list, '-OUQs', 'HOST-RESOURCES-MIB');

echo 'HR Stats:';

if (is_numeric($hrSystem[0]['hrSystemProcesses'])) {
    $rrd_file = $config['rrd_dir'].'/'.$device['hostname'].'/hr_processes.rrd';
    if (!is_file($rrd_file)) {
        rrdtool_create(
            $rrd_file,
            '--step 300 
            DS:procs:GAUGE:600:0:U '.$config['rrd_rra']
        );
    }

    $fields = array(
        'procs' => $hrSystem[0]['hrSystemProcesses'],
    );

    rrdtool_update($rrd_file, $fields);

    $tags = array();
    influx_update($device,'hr_processes',$tags,$fields);

    $graphs['hr_processes'] = true;
    echo ' Processes';
}

if (is_numeric($hrSystem[0]['hrSystemNumUsers'])) {
    $rrd_file = $config['rrd_dir'].'/'.$device['hostname'].'/hr_users.rrd';
    if (!is_file($rrd_file)) {
        rrdtool_create(
            $rrd_file,
            '--step 300 
            DS:users:GAUGE:600:0:U '.$config['rrd_rra']
        );
    }

    $fields = array(
        'users' => $hrSystem[0]['hrSystemNumUsers'],
    );

    rrdtool_update($rrd_file, $fields);

    $tags = array();
    influx_update($device,'hr_users',$tags,$fields);

    $graphs['hr_users'] = true;
    echo ' Users';
}

echo "\n";
