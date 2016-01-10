<?php

// Polls Apache statistics from script via SNMP
if (!empty($agent_data['app']['apache'])) {
    $apache = $agent_data['app']['apache'];
}
else {
    $options = '-O qv';
    $oid     = 'nsExtendOutputFull.6.97.112.97.99.104.101';
    $apache  = snmp_get($device, $oid, $options);
}

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-apache-'.$app['app_id'].'.rrd';

echo ' apache';

list ($total_access, $total_kbyte, $cpuload, $uptime, $reqpersec, $bytespersec,
    $bytesperreq, $busyworkers, $idleworkers, $score_wait, $score_start,
    $score_reading, $score_writing, $score_keepalive, $score_dns,
    $score_closing, $score_logging, $score_graceful, $score_idle, $score_open) = explode("\n", $apache);

if (!is_file($rrd_filename)) {
    rrdtool_create(
        $rrd_filename,
        '--step 300 
        DS:access:DERIVE:600:0:125000000000 
        DS:kbyte:DERIVE:600:0:125000000000 
        DS:cpu:GAUGE:600:0:125000000000 
        DS:uptime:GAUGE:600:0:125000000000 
        DS:reqpersec:GAUGE:600:0:125000000000 
        DS:bytespersec:GAUGE:600:0:125000000000 
        DS:byesperreq:GAUGE:600:0:125000000000 
        DS:busyworkers:GAUGE:600:0:125000000000 
        DS:idleworkers:GAUGE:600:0:125000000000 
        DS:sb_wait:GAUGE:600:0:125000000000 
        DS:sb_start:GAUGE:600:0:125000000000 
        DS:sb_reading:GAUGE:600:0:125000000000 
        DS:sb_writing:GAUGE:600:0:125000000000 
        DS:sb_keepalive:GAUGE:600:0:125000000000 
        DS:sb_dns:GAUGE:600:0:125000000000 
        DS:sb_closing:GAUGE:600:0:125000000000 
        DS:sb_logging:GAUGE:600:0:125000000000 
        DS:sb_graceful:GAUGE:600:0:125000000000 
        DS:sb_idle:GAUGE:600:0:125000000000 
        DS:sb_open:GAUGE:600:0:125000000000 '.$config['rrd_rra']
    );
}//end if

$fields = array(
                'access'       => $total_access,
                'kbyte'        => $total_kbyte,
                'cpu'          => $cpuload,
                'uptime'       => $uptime,
                'reqpersec'    => $reqpersec,
                'bytespersec'  => $bytespersec,
                'byesperreq'   => $bytesperreq,
                'busyworkers'  => $busyworkers,
                'idleworkers'  => $idleworkers,
                'sb_wait'      => $score_wait,
                'sb_start'     => $score_start,
                'sb_reading'   => $score_reading,
                'sb_writing'   => $score_writing,
                'sb_keepalive' => $score_keepalive,
                'sb_dns'       => $score_dns,
                'sb_closing'   => $score_closing,
                'sb_logging'   => $score_logging,
                'sb_graceful'  => $score_graceful,
                'sb_idle'      => $score_idle,
                'sb_open'      => $score_open,
);

rrdtool_update($rrd_filename, $fields);

$tags = array('name' => 'apache', 'app_id' => $app['app_id']);
influx_update($device,'app',$tags,$fields);

