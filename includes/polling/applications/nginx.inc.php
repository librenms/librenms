<?php

if (!empty($agent_data['app']['nginx'])) {
    $nginx = $agent_data['app']['nginx'];
}
else {
    // Polls nginx statistics from script via SNMP
    $nginx = snmp_get($device, 'nsExtendOutputFull.5.110.103.105.110.120', '-Ovq', 'NET-SNMP-EXTEND-MIB');
}

$nginx_rrd = $config['rrd_dir'].'/'.$device['hostname'].'/app-nginx-'.$app['app_id'].'.rrd';

echo " nginx statistics\n";

list($active, $reading, $writing, $waiting, $req) = explode("\n", $nginx);
if (!is_file($nginx_rrd)) {
    rrdtool_create(
        $nginx_rrd,
        '--step 300 
        DS:Requests:DERIVE:600:0:125000000000 
        DS:Active:GAUGE:600:0:125000000000 
        DS:Reading:GAUGE:600:0:125000000000 
        DS:Writing:GAUGE:600:0:125000000000 
        DS:Waiting:GAUGE:600:0:125000000000 '.$config['rrd_rra']
    );
}

print "active: $active reading: $reading writing: $writing waiting: $waiting Requests: $req";
$fields = array(
                'Requests' => $req,
                'Active'   => $active,
                'Reading'  => $reading,
                'Writing'  => $writing,
                'Waiting'  => $waiting,
);

rrdtool_update($nginx_rrd, $fields);

$tags = array('name' => 'nginx', 'app_id' => $app['app_id']);
influx_update($device,'app',$tags,$fields);

// Unset the variables we set here
unset($nginx);
unset($nginx_rrd);
unset($active);
unset($reading);
unset($writing);
unset($req);
