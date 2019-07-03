<?php

$simple_rrd = true;

foreach ((array)\LibreNMS\Config::get('nfsen_rrds', []) as $nfsenrrds) {
    if ($nfsenrrds[(strlen($nfsenrrds) - 1)] != '/') {
        $nfsenrrds .= '/';
    }

    // convert dots in filename to underscores
    $nfsensuffix = \LibreNMS\Config::get('nfsen_suffix', '');

    if (!empty(\LibreNMS\Config::get('nfsen_split_char'))) {
        $basefilename_underscored = preg_replace('/\./', \LibreNMS\Config::get('nfsen_split_char'), $device['hostname']);
    } else {
        $basefilename_underscored = $device['hostname'];
    }
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

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
