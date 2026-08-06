<?php

use LibreNMS\Billing;
use LibreNMS\Util\Number;

$datefrom = date('YmdHis', $vars['from'] ?? null);
$dateto = date('YmdHis', $vars['to'] ?? null);
$bill_id = $vars['id'] ?? 0;

$rates = Billing::getRates($bill_id, $datefrom, $dateto, $vars['dir'] ?? null);

$ports = dbFetchRows('SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D WHERE B.bill_id = ? AND P.port_id = B.port_id AND D.device_id = P.device_id', [$bill_id]);
$saps = App\Models\MplsSap::query()
    ->select('mpls_saps.*', 'devices.hostname')
    ->join('bill_saps', 'bill_saps.sap_id', '=', 'mpls_saps.sap_id')
    ->join('devices', 'devices.device_id', '=', 'mpls_saps.device_id')
    ->where('bill_saps.bill_id', $bill_id)
    ->get();

// Generate a list of ports and saps and then call the multi_bits grapher to generate from the list
$rrd_list = [];
$i = 0;

foreach ($ports as $port) {
    $rrd_file = get_port_rrdfile_path($port['hostname'], $port['port_id']);
    if (Rrd::checkRrdExists($rrd_file)) {
        $rrd_list[$i]['filename'] = $rrd_file;
        $rrd_list[$i]['descr'] = $port['ifDescr'];
        $i++;
    }
}

// SAP traffic is stored in bits (not octets) under different datasource names,
// so pass the datasource names and a divisor to normalise it to octets.
foreach ($saps as $sap) {
    $encap = $sap['sapEncapValue'] == '*' ? '4095' : $sap['sapEncapValue'];
    $rrd_file = Rrd::name($sap['hostname'], 'sap-' . $sap['svc_oid'] . '.' . $sap['sapPortId'] . '.' . $encap);
    if (Rrd::checkRrdExists($rrd_file)) {
        $rrd_list[$i]['filename'] = $rrd_file;
        $rrd_list[$i]['descr'] = $sap['sapDescription'] ?: ($sap['svc_oid'] . ' ' . $sap['ifName']);
        $rrd_list[$i]['ds_in'] = 'sapIngressBits';
        $rrd_list[$i]['ds_out'] = 'sapEgressBits';
        $rrd_list[$i]['divisor'] = 8;
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
$custom_graph = [];
if ($bill['bill_type'] == 'cdr') {
    $custom_graph[] = 'COMMENT:\\r';
    $custom_graph[] = 'HRULE:' . $rates['rate_95th'] . "#cc0000:95th %ile \: " . Number::formatSi($rates['rate_95th'], 2, 0,
        'bps') . ' (' . $rates['dir_95th'] . ') (CDR\: ' . Number::formatSi($bill['bill_cdr'], 2, 0, 'bps') . ')';
    $custom_graph[] = 'HRULE:' . ($rates['rate_95th'] * -1) . '#cc0000';
} elseif ($bill['bill_type'] == 'quota') {
    $custom_graph[] = 'COMMENT:\\r';
    $custom_graph[] = 'HRULE:' . $rates['rate_average'] . "#cc0000:'Usage \: " . Billing::formatBytes($rates['total_data']) . ' (' . Number::formatSi($rates['rate_average'], 2, 0, 'bps') . ")'";
    $custom_graph[] = 'HRULE:' . ($rates['rate_average'] * -1) . '#cc0000';
}

require 'includes/html/graphs/generic_multi_bits_separated.inc.php';
