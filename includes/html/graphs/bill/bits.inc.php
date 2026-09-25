<?php

use App\Models\Bill;
use LibreNMS\Billing;
use LibreNMS\Util\Number;

$datefrom = date('YmdHis', $vars['from'] ?? null);
$dateto = date('YmdHis', $vars['to'] ?? null);
$bill_id = $vars['id'] ?? 0;

$rates = Billing::getRates($bill_id, $datefrom, $dateto, $vars['dir'] ?? null);

$bill = Bill::find($bill_id);

// Generate a list of source rrds and then call the multi_bits grapher to generate from the list.
// Sources may store octets or bits, so each carries its own dataset names and multiplier.
$rrd_list = [];
foreach ($bill?->billableSources() ?? [] as $source) {
    $rrd = $source->getBillingRrd();
    if ($rrd && Rrd::checkRrdExists($rrd['filename'])) {
        $rrd_list[] = $rrd + ['descr' => $source->getBillingLabel()];
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
