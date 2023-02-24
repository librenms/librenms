<?php

use LibreNMS\Util\Number;

$datefrom = date('YmdHis', $vars['from']);
$dateto = date('YmdHis', $vars['to']);

$rates = getRates($vars['id'], $datefrom, $dateto, $vars['dir']);

$ports = dbFetchRows('SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D WHERE B.bill_id = ? AND P.port_id = B.port_id AND D.device_id = P.device_id', [$vars['id']]);

// Generate a list of ports and then call the multi_bits grapher to generate from the list
$i = 0;

foreach ($ports as $port) {
    $rrd_file = get_port_rrdfile_path($port['hostname'], $port['port_id']);
    if (Rrd::checkRrdExists($rrd_file)) {
        $rrd_list[$i]['filename'] = $rrd_file;
        $rrd_list[$i]['descr'] = $port['ifDescr'];
        $i++;
    }
}

$units = 'bps';
$total_units = 'B';
$colours_in = 'greens';
$multiplier = '8';
$colours_out = 'blues';

$nototal = 1;
$ds_in = 'INOCTETS';
$ds_out = 'OUTOCTETS';

// print_r($rates);
if ($bill['bill_type'] == 'cdr') {
    $custom_graph = " COMMENT:'\\r' ";
    $custom_graph .= ' HRULE:' . $rates['rate_95th'] . "#cc0000:'95th %ile \: " . Number::formatSi($rates['rate_95th'], 2, 3,
            'bps') . ' (' . $rates['dir_95th'] . ') (CDR\: ' . Number::formatSi($bill['bill_cdr'], 2, 3, 'bps') . ")'";
    $custom_graph .= ' HRULE:' . ($rates['rate_95th'] * -1) . '#cc0000';
} elseif ($bill['bill_type'] == 'quota') {
    $custom_graph = " COMMENT:'\\r' ";
    $custom_graph .= ' HRULE:' . $rates['rate_average'] . "#cc0000:'Usage \: " . format_bytes_billing($rates['total_data']) . ' (' . Number::formatSi($rates['rate_average'], 2, 3, 'bps') . ")'";
    $custom_graph .= ' HRULE:' . ($rates['rate_average'] * -1) . '#cc0000';
}

require 'includes/html/graphs/generic_multi_bits_separated.inc.php';
