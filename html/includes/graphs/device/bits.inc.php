<?php

// Generate a list of ports and then call the multi_bits grapher to generate from the list
$ds_in  = 'INOCTETS';
$ds_out = 'OUTOCTETS';

foreach (dbFetchRows('SELECT * FROM `ports` WHERE `device_id` = ?', array($device['device_id'])) as $port) {
    $ignore = 0;
    if (is_array($config['device_traffic_iftype'])) {
        foreach ($config['device_traffic_iftype'] as $iftype) {
            if (preg_match($iftype.'i', $port['ifType'])) {
                $ignore = 1;
            }
        }
    }

    if (is_array($config['device_traffic_descr'])) {
        foreach ($config['device_traffic_descr'] as $ifdescr) {
            if (preg_match($ifdescr.'i', $port['ifDescr']) || preg_match($ifdescr.'i', $port['ifName']) || preg_match($ifdescr.'i', $port['portName'])) {
                $ignore = 1;
            }
        }
    }

    $rrd_filename = get_port_rrdfile_path ($device['hostname'], $port['port_id']);
    if ($ignore != 1 && rrdtool_check_rrd_exists($rrd_filename)) {
        $port = ifLabel($port);
        // Fix Labels! ARGH. This needs to be in the bloody database!
        $rrd_filenames[]           = $rrd_filename;
        $rrd_list[$i]['filename']  = $rrd_filename;
        $rrd_list[$i]['descr']     = shorten_interface_type($port['label']);
        $rrd_list[$i]['descr_in']  = $port['label'];
        $rrd_list[$i]['descr_out'] = $port['ifAlias'];
        $rrd_list[$i]['ds_in']     = $ds_in;
        $rrd_list[$i]['ds_out']    = $ds_out;
        $i++;
    }

    unset($ignore);
}//end foreach

$units       = 'b';
$total_units = 'B';
$colours_in  = 'greens';
$multiplier  = '8';
$colours_out = 'blues';

// $nototal = 1;
$ds_in  = 'INOCTETS';
$ds_out = 'OUTOCTETS';

$graph_title .= '::bits';

$colour_line_in  = '006600';
$colour_line_out = '000099';
$colour_area_in  = '91B13C';
$colour_area_out = '8080BD';

require 'includes/graphs/generic_multi_seperated.inc.php';

// include("includes/graphs/generic_multi_bits_separated.inc.php");
// include("includes/graphs/generic_multi_data_separated.inc.php");
