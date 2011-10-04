<?php

## Generate a list of ports and then call the multi_bits grapher to generate from the list

$i = 0;

foreach ($ports as $port)
{
  if (is_file($config['rrd_dir'] . "/" . $port['hostname'] . "/port-" . safename($port['ifIndex'] . ".rrd")))
  {
    $rrd_list[$i]['filename'] = $config['rrd_dir'] . "/" . $port['hostname'] . "/port-" . safename($port['ifIndex'] . ".rrd");
    $rrd_list[$i]['descr'] = $port['ifDescr'];
    $i++;
  }
}

$units='bps';
$total_units='B';
$colours_in='greens';
$multiplier = "8";
$colours_out = 'blues';

$nototal = 1;
$ds_in  = "INOCTETS";
$ds_out = "OUTOCTETS";

#print_r($rates);

if($bill['bill_type'] == "cdr") {
   $custom_graph = " COMMENT:'\\r' ";
   $custom_graph .= " HRULE:" . $rates['rate_95th'] * 1000 . "#cc0000:'95th %ile \: ".formatRates($rates['rate_95th']*1000)." (".$rates['dir_95th'].") (CDR\: ".formatRates($bill['bill_cdr']*1000).")'";
   $custom_graph .= " HRULE:" . $rates['rate_95th'] * 1000 * -1 . "#cc0000";
} elseif($bill['bill_type'] == "quota") {
   $custom_graph = " COMMENT:'\\r' ";
   $custom_graph .= " HRULE:" . $rates['rate_average'] * 1000 . "#cc0000:'Usage \: ".formatStorage($rates['total_data']*1024*1024)." (".formatRates($rates['rate_average']*1000).")'";
   $custom_graph .= " HRULE:" . $rates['rate_average'] * 1000 * -1 . "#cc0000";
}

include("includes/graphs/generic_multi_bits_separated.inc.php");

?>
