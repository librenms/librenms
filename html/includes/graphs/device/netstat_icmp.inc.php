<?php

include("includes/graphs/common.inc.php");

$device = device_by_id_cache($id);

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/netstats-icmp.rrd";

$stats = array('icmpInMsgs'      => '00cc00',
               'icmpOutMsgs'     => '006600',
               'icmpInErrors'    => 'cc0000',
               'icmpOutErrors'   => '660000',
               'icmpInEchos'     => '0066cc',
               'icmpOutEchos'    => '003399',
               'icmpInEchoReps'  => 'cc00cc',
               'icmpOutEchoReps' => '990099');

$i=0;

foreach($stats as $stat => $colour)
{
  $i++;
  $rrd_list[$i]['filename'] = $rrd_filename;
  $rrd_list[$i]['descr'] = str_replace("icmp", "", $stat);
  $rrd_list[$i]['rra'] = $stat;
  if(strpos($stat, "Out") !== FALSE)
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
