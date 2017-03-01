<?php

$simple_rrd = true;

if (!is_array($config['nfsen_rrds'])) {
    $config['nfsen_rrds'] = array($config['nfsen_rrds']);
}

foreach ($config['nfsen_rrds'] as $nfsenrrds) {
    $nfsenrrds = rtrim($nfsenrrds, '/') . '/';

    $nfsenHostname=$device['hostname'];
    if ($config['nfsen_split_char']) {
        $nfsenHostname=str_replace('.', $config['nfsen_split_char'], $device['hostname']);
    }
    $rrd_filename=$nfsenrrds.$nfsenHostname.'/'.$vars['channel'].'.rrd';

    if (is_file($rrd_filename)) {

        $flowtypes = array('tcp', 'udp', 'icmp', 'other');

        $colours   = 'blues';
        $nototal   = 0;
        $units     = '';
        $scale_min = '0';
        $unit_text = $dsdescr;

        // set a multiplier which in turn will create a CDEF if this var is set
        if ($dsprefix == 'traffic_') {
            $multiplier = '8';
        }

        $rrd_list   = array();
        $nfsen_iter = 1;
        foreach ($flowtypes as $flowtype) {
            $rrd_list[] = array(
                'filename'  => $rrd_filename,
                'descr' => $flowtype,
                'ds' => $dsprefix.$flowtype
            );
            d_echo($rrd_list);

            $nfsen_iter++;
        }
    }
}

require 'includes/graphs/generic_multi_simplex_seperated.inc.php';
