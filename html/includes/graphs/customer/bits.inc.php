<?php

// Generate a list of ports and then call the multi_bits grapher to generate from the list
$i = 0;

if (!is_array($config['customers_descr'])) {
    $config['customers_descr'] = array($config['customers_descr']);
}

$descr_type = "'".implode("', '", $config['customers_descr'])."'";

foreach (dbFetchRows('SELECT * FROM `ports` AS I, `devices` AS D WHERE `port_descr_type` IN (?) AND `port_descr_descr` = ? AND D.device_id = I.device_id', array(array($descr_type), $vars['id'])) as $port) {
    $rrd_filename = get_port_rrdfile_path ($port['hostname'], $port['port_id']); // FIXME: Unification OK?
    if (rrdtool_check_rrd_exists($rrd_filename)) {
        $rrd_list[$i]['filename']  = $rrd_filename;
        $rrd_list[$i]['descr']     = $port['hostname'].'-'.$port['ifDescr'];
        $rrd_list[$i]['descr_in']  = shorthost($port['hostname']);
        $rrd_list[$i]['descr_out'] = makeshortif($port['ifDescr']);
        $i++;
    }
}

$units       = 'bps';
$total_units = 'B';
$colours_in  = 'greens';
$multiplier  = '8';
$colours_out = 'blues';

$nototal = 1;

$ds_in  = 'INOCTETS';
$ds_out = 'OUTOCTETS';

require 'includes/graphs/generic_multi_bits_separated.inc.php';
