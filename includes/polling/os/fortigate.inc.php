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


// Start Aggregate discovery for CPU/NPU drops 

$droprrd = $config['rrd_dir'].'/'.$device['hostname'].'/fortigate_npu_drops.rrd';
$num_cpu   = snmp_get($device, 'FORTINET-FORTIGATE-MIB::fgProcessorCount.0', '-Ovq');
for($i = 1; $i <= $num_cpu; $i++) {
       $cpurrd    = $config['rrd_dir'].'/'.$device['hostname'].'/processor-fortigate-fixed-'.$i.'.rrd';
       $npu_drop = snmp_get($device, "FORTINET-FORTIGATE-MIB::fgProcessorPktDroppedCount.$i", '-Ovq');
       $npu_total += $npu_drop; 
       print "NPU: $npu_drop - DROPS: $npu_total\n";
       if (!is_file($droprrd)) {
           print "$cpurrd not found\n";
           rrdtool_create($droprrd, ' --step 300 DS:DROP:GAUGE:600:-1:100 '.$config['rrd_rra']);
       }
}

$fields = array( 'DROP' => $npu_total );
rrdtool_update($cpurrd, $fields);
$graphs['fortigate_npu_drops'] = true;

// Start somewhat automated discovery for processors in the chassis

$cpurrd    = $config['rrd_dir'].'/'.$device['hostname'].'/fortigate_cpu.rrd';
#$cpu_usage = snmp_get($device, 'FORTINET-FORTIGATE-MIB::fgSysCpuUsage.0', '-Ovq');

print "NUM CPU: $num_cpu\n";

// Fortigate have a pretty logical CPU index going on. It's predictable. 
for($i = 1; $i <= $num_cpu; $i++) {
       $cpurrd    = $config['rrd_dir'].'/'.$device['hostname'].'/processor-fortigate-fixed-'.$i.'.rrd';
       $cpu_usage = snmp_get($device, "FORTINET-FORTIGATE-MIB::fgProcessorUsage.$i", '-Ovq');
       $usage = trim ( str_replace(" %", "", $cpu_usage ) ) ;
       print "CPU: $num_cpu - USAGE: $usage\n";
       if (!is_file($cpurrd)) {
           print "$cpurrd not found\n";
           rrdtool_create($cpurrd, ' --step 300 DS:LOAD:GAUGE:600:-1:100 '.$config['rrd_rra']);
       }
       $fields = array( 'LOAD' => $usage );
       rrdtool_update($cpurrd, $fields);
}

$graphs['fortigate_cpu'] = true;


