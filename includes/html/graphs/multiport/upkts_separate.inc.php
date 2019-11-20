<?php

$i = 0;

foreach (explode(',', $vars['id']) as $ifid) {
    $port = dbFetchRow('SELECT * FROM `ports` AS I, devices as D WHERE I.port_id = ? AND I.device_id = D.device_id', array($ifid));
    $rrd_file = get_port_rrdfile_path($port['hostname'], $ifid);
    if (rrdtool_check_rrd_exists($rrd_file)) {
        $port = cleanPort($port);
        $rrd_list[$i]['filename']  = $rrd_file;
        $rrd_list[$i]['descr']     = format_hostname($port, $port['hostname']).' '.$port['ifDescr'];
        $rrd_list[$i]['descr_in']  = format_hostname($port, $port['hostname']);
        $rrd_list[$i]['descr_out'] = makeshortif($port['label']);
        $rrd_list[$i]['ds_in'] = 'INUCASTPKTS';
        $rrd_list[$i]['ds_out'] = 'OUTUCASTPKTS';
        $rrd_list[$i]['colour_area_in']  = 'AA66AA';
        $rrd_list[$i]['colour_area_out'] = 'FFDD88';
        $i++;
    }
}

$units       = 'pps';
$unit_text = 'Packets';
$total_units = 'PPS';
$colours_in  = 'purples';
$multiplier  = '1';
$colours_out = 'oranges';

$args['nototal'] = 1;

require 'includes/html/graphs/generic_multi_seperated.inc.php';
