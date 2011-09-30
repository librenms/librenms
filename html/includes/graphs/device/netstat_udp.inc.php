<?php

include("includes/graphs/common.inc.php");
$device = device_by_id_cache($id);

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/netstats-udp.rrd";

$stats = array('udpInDatagrams','udpOutDatagrams','udpInErrors','udpNoPorts');

$i=0;
foreach($stats as $stat)
{
  $i++;
  $rrd_list[$i]['filename'] = $rrd_filename;
  $rrd_list[$i]['descr'] = str_replace("udp", "", $stat);
  $rrd_list[$i]['ds'] = $stat;
  if (strpos($stat, "Out") !== FALSE || strpos($stat, "Retrans") !== FALSE || strpos($stat, "Attempt") !== FALSE)
  {
    $rrd_list[$i]['invert'] = TRUE;
  }
}

$colours='mixed';

$nototal = 1;
$simple_rrd = 1;

include("includes/graphs/generic_multi_line.inc.php");

?>
