<?php

$datefrom = date('YmdHis', $vars['from']);
$dateto   = date('YmdHis', $vars['to']);

$rates = getRates($vars['id'], $datefrom, $dateto, $vars['dir']);

$ports = dbFetchRows('SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D WHERE B.bill_id = ? AND P.port_id = B.port_id AND D.device_id = P.device_id', array($vars['id']));

// Generate a list of ports and then call the multi_bits grapher to generate from the list
$i = 0;

foreach ($ports as $port) {
    $rrd_file = get_port_rrdfile_path($port['hostname'], $port['port_id']);
    if (rrdtool_check_rrd_exists($rrd_file)) {
        $rrd_list[$i]['filename'] = $rrd_file;
        $rrd_list[$i]['descr']    = $port['ifDescr'];
        $i++;
    }
}

$units       = 'bps';
$total_units = 'B';
$colours_in  = 'greens';
$multiplier  = '8';
$colours_out = 'blues';

$nototal = 1;
$ds_in   = 'INOCTETS';
$ds_out  = 'OUTOCTETS';

// print_r($rates);
if ($bill['bill_type'] == 'cdr') {
    $custom_graph  = " COMMENT:'\\r' ";
    $custom_graph .= ' HRULE:'.$rates['rate_95th']."#cc0000:'95th %ile \: ".formatRates($rates['rate_95th']).' ('.$rates['dir_95th'].') (CDR\: '.formatRates($bill['bill_cdr']).")'";
    $custom_graph .= ' HRULE:'.($rates['rate_95th'] * -1).'#cc0000';
} elseif ($bill['bill_type'] == 'quota') {
    $custom_graph  = " COMMENT:'\\r' ";
    $custom_graph .= ' HRULE:'.$rates['rate_average']."#cc0000:'Usage \: ".format_bytes_billing($rates['total_data']).' ('.formatRates($rates['rate_average']).")'";
    $custom_graph .= ' HRULE:'.($rates['rate_average'] * -1).'#cc0000';
}

require 'includes/html/graphs/generic_multi_bits_separated.inc.php';
