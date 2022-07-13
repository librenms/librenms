<?php

// Generate a list of ports and then call the multi_bits grapher to generate from the list
$i = 0;

foreach (dbFetchRows('SELECT * FROM `ports` AS P, `devices` AS D WHERE D.device_id = P.device_id ORDER BY P.ifInOctets_rate DESC') as $port) {
    $ignore = 0;
    if (is_array(\LibreNMS\Config::get('device_traffic_iftype'))) {
        foreach (\LibreNMS\Config::get('device_traffic_iftype') as $iftype) {
            if (preg_match($iftype . 'i', $port['ifType'])) {
                $ignore = 1;
            }
        }
    }

    if (is_array(\LibreNMS\Config::get('device_traffic_descr'))) {
        foreach (\LibreNMS\Config::get('device_traffic_descr') as $ifdescr) {
            if (preg_match($ifdescr . 'i', $port['ifDescr']) || preg_match($ifdescr . 'i', $port['ifName'])) {
                $ignore = 1;
            }
        }
    }

    $rrd_filename = get_port_rrdfile_path($port['hostname'], $port['port_id']);
    if (! $ignore && $i < 1100 && Rrd::checkRrdExists($rrd_filename)) {
        $rrd_filenames[] = $rrd_filename;
        $rrd_list[$i]['filename'] = $rrd_filename;
        // $rrd_list[$i]['descr'] = $port['device_id'] . " " . $port['ifDescr'];
        $rrd_list[$i]['descr'] = 'dev' . $port['device_id'] . 'if' . $port['ifIndex'];
        $rrd_list[$i]['rra_in'] = $rra_in;
        $rrd_list[$i]['rra_out'] = $rra_out;
        $i++;
    }

    unset($ignore);
}//end foreach

$units = 'bps';
$total_units = 'B';
$colours_in = 'greens';
$multiplier = '8';
$colours_out = 'blues';

$nototal = 1;

$ds_in = 'INOCTETS';
$ds_out = 'OUTOCTETS';

$graph_title .= '::bits';

$colour_line_in = '006600';
$colour_line_out = '000099';
$colour_area_in = 'CDEB8B';
$colour_area_out = 'C3D9FF';

require 'includes/html/graphs/generic_multi_bits_separated.inc.php';
