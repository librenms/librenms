<?php

$version = preg_replace('/(.+)\ version\ (.+)\ \(SN:\ (.+)\,\ (.+)\)/', '\\1||\\2||\\3||\\4', $poll_device['sysDescr']);
list($hardware,$version,$serial,$features) = explode('||', $version);

$sessrrd   = $config['rrd_dir'].'/'.$device['hostname'].'/screenos_sessions.rrd';
$sess_cmd  = $config['snmpget'].' -M '.$config['mibdir'].' -O qv '.snmp_gen_auth($device).' '.$device['hostname'];
$sess_cmd .= ' .1.3.6.1.4.1.3224.16.3.2.0 .1.3.6.1.4.1.3224.16.3.3.0 .1.3.6.1.4.1.3224.16.3.4.0';
$sess_data = shell_exec($sess_cmd);
list ($sessalloc, $sessmax, $sessfailed) = explode("\n", $sess_data);

if (!is_file($sessrrd)) {
    rrdtool_create(
        $sessrrd,
        ' --step 300 
        DS:allocate:GAUGE:600:0:3000000 
        DS:max:GAUGE:600:0:3000000 
        DS:failed:GAUGE:600:0:1000 '.$config['rrd_rra']
    );
}

$fields = array(
    'allocate'  => $sessalloc,
    'max'       => $sessmax,
    'failed'    => $sessfailed,
);

rrdtool_update("$sessrrd", $fields);

$tags = array();
influx_update($device,'screenos_sessions',$tags,$fields);

$graphs['screenos_sessions'] = true;
