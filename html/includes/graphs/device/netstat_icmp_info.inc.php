<?php

include('includes/graphs/common.inc.php');

$device = device_by_id_cache($id);

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/netstats-icmp.rrd";

$stats = array('icmpInSrcQuenchs' => array(),
               'icmpOutSrcQuenchs' => array(),
               'icmpInRedirects' => array(),
               'icmpOutRedirects' => array(),
               'icmpInAddrMasks' => array(),
               'icmpOutAddrMasks' => array(),
               'icmpInAddrMaskReps' => array(),
               'icmpOutAddrMaskReps' => array());

$i=0;

foreach($stats as $stat => $array)
{
  $i++;
  $rrd_list[$i]['filename'] = $rrd_filename;
  $rrd_list[$i]['descr'] = str_replace("icmp", "", $stat);
  $rrd_list[$i]['ds'] = $stat;
  if (strpos($stat, "Out") !== FALSE)
  {
    $rrd_list[$i]['invert'] = TRUE;
  }
}

$colours='mixed';

$scale_min = "0";
$nototal = 1;
$basicrrd = 1;

include("includes/graphs/generic_multi_line.inc.php");

?>
