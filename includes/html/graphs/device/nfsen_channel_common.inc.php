<?php

$simple_rrd = true;

foreach ((array) \LibreNMS\Config::get('nfsen_rrds', []) as $nfsenrrds) {
    if ($nfsenrrds[strlen($nfsenrrds) - 1] != '/') {
        $nfsenrrds .= '/';
    }

    $nfsen_filename = nfsen_hostname($device['hostname']);

    if (is_file($nfsenrrds . $nfsen_filename . '/' . $vars['channel'] . '.rrd')) {
        $rrd_filename = $nfsenrrds . $nfsen_filename . '/' . $vars['channel'] . '.rrd';

        $flowtypes = ['tcp', 'udp', 'icmp', 'other'];

        $rrd_list = [];
        $nfsen_iter = 1;
        foreach ($flowtypes as $flowtype) {
            $rrd_list[$nfsen_iter]['filename'] = $rrd_filename;
            $rrd_list[$nfsen_iter]['descr'] = $flowtype;
            $rrd_list[$nfsen_iter]['ds'] = $dsprefix . $flowtype;

            // set a multiplier which in turn will create a CDEF if this var is set
            if ($dsprefix == 'traffic_') {
                $multiplier = '8';
            }

            $colours = 'blues';
            $nototal = 0;
            $units = '';
            $unit_text = $dsdescr;
            $scale_min = '0';

            $nfsen_iter++;
        }
    }
}

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
