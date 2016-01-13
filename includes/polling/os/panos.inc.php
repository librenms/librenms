<?php

$hardware = trim(snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.2.1.0', '-OQv', '', ''), '" ');
$version  = trim(snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.1.1.0', '-OQv', '', ''), '" ');
$serial   = trim(snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.1.3.0', '-OQv', '', ''), '" ');

// list(,,,$hardware) = explode (" ", $poll_device['sysDescr']);
$sessrrd  = $config['rrd_dir'].'/'.$device['hostname'].'/panos-sessions.rrd';
$sessions = snmp_get($device, '1.3.6.1.4.1.25461.2.1.2.3.3.0', '-Ovq');

if (is_numeric($sessions)) {
    if (!is_file($sessrrd)) {
        rrdtool_create($sessrrd, ' --step 300 DS:sessions:GAUGE:600:0:3000000 '.$config['rrd_rra']);
    }

    $fields = array(
        'sessions' => $sessions,
    );

    rrdtool_update($sessrrd, $fields);

    $tags = array();
    influx_update($device,'panos-sessions',$tags,$fields);

    $graphs['panos_sessions'] = true;
}
