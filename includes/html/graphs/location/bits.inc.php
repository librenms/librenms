<?php

// Generate a list of ports and then call the multi_bits grapher to generate from the list
$ds_in = 'INOCTETS';
$ds_out = 'OUTOCTETS';

$i = 1;
foreach ($devices as $device) {
    foreach (dbFetchRows('SELECT * FROM `ports` WHERE `device_id` = ? AND `disabled` = 0', [$device['device_id']]) as $int) {
        $ignore = 0;
        if (is_array(\LibreNMS\Config::get('device_traffic_iftype'))) {
            foreach (\LibreNMS\Config::get('device_traffic_iftype') as $iftype) {
                if (preg_match($iftype . 'i', $int['ifType'])) {
                    $ignore = 1;
                }
            }
        }

        if (is_array(\LibreNMS\Config::get('device_traffic_descr'))) {
            foreach (\LibreNMS\Config::get('device_traffic_descr') as $ifdescr) {
                if (preg_match($ifdescr . 'i', $int['ifDescr']) || preg_match($ifdescr . 'i', $int['ifName'])) {
                    $ignore = 1;
                }
            }
        }

        $rrd_file = get_port_rrdfile_path($device['hostname'], $int['port_id']);
        if (Rrd::checkRrdExists($rrd_file) && $ignore != 1) {
            $rrd_filename = $rrd_file; // FIXME: Can this be unified without side-effects?
            $rrd_list[$i]['filename'] = $rrd_filename;
            $rrd_list[$i]['descr'] = $port['label'];
            $rrd_list[$i]['descr_in'] = $device['hostname'];
            $rrd_list[$i]['descr_out'] = \LibreNMS\Util\Clean::html($port['ifAlias'], []);
            $rrd_list[$i]['ds_in'] = $ds_in;
            $rrd_list[$i]['ds_out'] = $ds_out;
            $i++;
        }

        unset($ignore);
    }//end foreach
}//end foreach

$units = 'b';
$total_units = 'B';
$colours_in = 'greens';
$multiplier = '8';
$colours_out = 'blues';

$nototal = 1;

$graph_title .= '::bits';

$colour_line_in = '006600';
$colour_line_out = '000099';
$colour_area_in = 'CDEB8B';
$colour_area_out = 'C3D9FF';

require 'includes/html/graphs/generic_multi_bits_separated.inc.php';
