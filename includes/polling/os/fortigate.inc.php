<?php

$fnSysVersion = snmp_get($device, 'FORTINET-FORTIGATE-MIB::fgSysVersion.0', '-Ovq');
$serial       = snmp_get($device, 'ENTITY-MIB::entPhysicalSerialNum.1', '-Ovq');

$version                 = preg_replace('/(.+),(.+),(.+)/', '\\1||\\2||\\3', $fnSysVersion);
list($version,$features) = explode('||', $version);

if (isset($rewrite_fortinet_hardware[$poll_device['sysObjectID']])) {
    $hardware = $rewrite_fortinet_hardware[$poll_device['sysObjectID']];
}

if (empty($hardware)) {
    $hardware = snmp_get($device, 'ENTITY-MIB::entPhysicalModelName.1', '-Ovq');
}

$sessrrd  = $config['rrd_dir'].'/'.$device['hostname'].'/fortigate_sessions.rrd';
$sessions = snmp_get($device, 'FORTINET-FORTIGATE-MIB::fgSysSesCount.0', '-Ovq');

if (is_numeric($sessions)) {
    if (!is_file($sessrrd)) {
        rrdtool_create($sessrrd, ' --step 300 DS:sessions:GAUGE:600:0:3000000 '.$config['rrd_rra']);
    }

    print "Sessions: $sessions\n";

    $fields = array(
        'sessions' => $sessions,
    );

    rrdtool_update($sessrrd, $fields);

    $graphs['fortigate_sessions'] = true;
}


