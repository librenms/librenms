<?php

$simple_rrd = true;

if (!is_array($config['nfsen_rrds'])) {
    $config['nfsen_rrds'] = array($config['nfsen_rrds']);
}

foreach ($config['nfsen_rrds'] as $nfsenrrds) {
    $nfsenrrds = rtrim($nfsenrrds, '/') . '/';

    $rrd_filename=$nfsenrrds.nfsen_hostname($device['hostname']).'/'.$vars['channel'].'.rrd';

    if (is_file($rrd_filename)) {
        $colours   = 'blues';
        $nototal   = 0;
        $units     = '';
        $scale_min = '0';
        $unit_text = $dsdescr;

        // set a multiplier which in turn will create a CDEF if this var is set
        if ($dsprefix == 'traffic_') {
            $multiplier = '8';
        }

        $flowtypes = array('tcp', 'udp', 'icmp', 'other');
        $rrd_list   = array();
        foreach ($flowtypes as $flowtype) {
            $rrd_list[] = array(
                'filename'  => $rrd_filename,
                'descr' => $flowtype,
                'ds' => $dsprefix.$flowtype
            );
        }
        d_echo($rrd_list);
    }
}

require 'includes/graphs/generic_multi_simplex_seperated.inc.php';
