<?php

$simple_rrd = true;

if (!is_array($config['nfsen_rrds'])) {
    $config['nfsen_rrds'] = array($config['nfsen_rrds']);
}

foreach ($config['nfsen_rrds'] as $nfsenrrds) {
    if ($nfsenrrds[(strlen($nfsenrrds) - 1)] != '/') {
        $nfsenrrds .= '/';
    }

    // convert dots in filename to underscores
    $nfsensuffix = '';
    if ($config['nfsen_suffix']) {
        $nfsensuffix = $config['nfsen_suffix'];
    }

    $basefilename_underscored = preg_replace('/\./', $config['nfsen_split_char'], $device['hostname']);
    $nfsen_filename           = preg_replace('/'.$nfsensuffix.'/', '', $basefilename_underscored);

    if (is_file($nfsenrrds.$nfsen_filename.'.rrd')) {
        $rrd_filename = $nfsenrrds.$nfsen_filename.'.rrd';

        $flowtypes = array('tcp', 'udp', 'icmp', 'other');

        $rrd_list   = array();
        $nfsen_iter = 1;
        foreach ($flowtypes as $flowtype) {
            $rrd_list[$nfsen_iter]['filename']  = $rrd_filename;
            $rrd_list[$nfsen_iter]['descr'] = $flowtype;
            $rrd_list[$nfsen_iter]['ds']    = $dsprefix.$flowtype;

            // set a multiplier which in turn will create a CDEF if this var is set
            if ($dsprefix == 'traffic_') {
                $multiplier = '8';
            }

            $colours   = 'blues';
            $nototal   = 0;
            $units     = '';
            $unit_text = $dsdescr;
            $scale_min = '0';

            if ($_GET['debug']) {
                print_r($rrd_list);
            }

            $nfsen_iter++;
        }
    }
}

require 'includes/graphs/generic_multi_simplex_seperated.inc.php';
