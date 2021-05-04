<?php

// Generate a list of ports and then call the multi_bits grapher to generate from the list
$ds_in = 'INOCTETS';
$ds_out = 'OUTOCTETS';

$ports = dbFetchRows('SELECT * FROM `ports` WHERE `device_id` = ? AND `disabled` = 0 AND `deleted` = 0', [$device['device_id']]);

if (empty($ports)) {
    graph_text_and_exit('No Ports');
}

foreach ($ports as $port) {
    $ignore = 0;
    if (is_array(\LibreNMS\Config::get('device_traffic_iftype'))) {
        foreach (\LibreNMS\Config::get('device_traffic_iftype') as $iftype) {
            if ($iftype == '/l2vlan/' && $device['os'] == 'asa') {
                // ASA (at least in multicontext) reports all interfaces as l2vlan even if they are l3
                // so every context has no graph displayed unless l2vlan are accepted for all.
                // This patch will ignore l2vlan for ASA.
                continue;
            }
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

    $rrd_filename = get_port_rrdfile_path($device['hostname'], $port['port_id']);
    if ($ignore != 1 && Rrd::checkRrdExists($rrd_filename)) {
        $port = cleanPort($port);
        // Fix Labels! ARGH. This needs to be in the bloody database!
        $rrd_filenames[] = $rrd_filename;
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = \LibreNMS\Util\Rewrite::shortenIfType($port['label']);
        $rrd_list[$i]['descr_in'] = $port['label'];
        $rrd_list[$i]['descr_out'] = \LibreNMS\Util\Clean::html($port['ifAlias'], []);
        $rrd_list[$i]['ds_in'] = $ds_in;
        $rrd_list[$i]['ds_out'] = $ds_out;
        $i++;
    }

    unset($ignore);
}//end foreach

$units = 'b';
$total_units = 'B';
$colours_in = 'greens';
$multiplier = '8';
$colours_out = 'purples';

// $nototal = 1;
$ds_in = 'INOCTETS';
$ds_out = 'OUTOCTETS';

$graph_title .= '::bits';

$colour_line_in = '006600';
$colour_line_out = '000099';
$colour_area_in = '91B13C';
$colour_area_out = '8080BD';

require 'includes/html/graphs/generic_multi_seperated.inc.php';

// include("includes/html/graphs/generic_multi_bits_separated.inc.php");
// include("includes/html/graphs/generic_multi_data_separated.inc.php");
