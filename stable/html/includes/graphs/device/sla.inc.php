<?php

include("includes/graphs/common.inc.php");

$sla = dbFetchRow("SELECT * FROM `slas` WHERE `sla_id` = ?", array($id));
$device = device_by_id_cache($sla['device_id']);

#if ($_GET['width'] >= "450") { $descr_len = "48"; } else { $descr_len = "21"; }
$descr_len = intval($_GET['width'] / 8) * 0.8;

$unit_long = 'milliseconds';
$unit = 'ms';

$rrd_options .= " -l 0 -E ";
$rrd_options .= " COMMENT:'".str_pad($unit_long,$descr_len)."   Cur      Min     Max\\n'";

$name = "";
if ($sla['tag'])
  $name .= $sla['tag'];
if ($sla['owner'])
  $name .= " (Owner: ". $sla['owner'] .")";

$descr_fixed = substr(str_pad($name, $descr_len-3),0,$descr_len-3);
$avg_fixed = substr(str_pad('Average', $descr_len-3),0,$descr_len-3);

$rrd_file  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("sla-" . $sla['sla_nr'] . ".rrd");

$rrd_options .= " DEF:rtt=$rrd_file:rtt:AVERAGE ";
$rrd_options .= " VDEF:avg=rtt,AVERAGE ";
$rrd_options .= " LINE1:avg#CCCCFF:'".$avg_fixed."':dashes";
$rrd_options .= " GPRINT:rtt:AVERAGE:%4.1lf".$unit."\\\l ";
$rrd_options .= " LINE1:rtt#CC0000:'" . str_replace(':','\:',str_replace('\*','*',$descr_fixed)) . "'";
$rrd_options .= " GPRINT:rtt:LAST:%4.1lf".$unit." ";
$rrd_options .= " GPRINT:rtt:MIN:%4.1lf".$unit." ";
$rrd_options .= " GPRINT:rtt:MAX:%4.1lf".$unit."\\\l ";

?>
