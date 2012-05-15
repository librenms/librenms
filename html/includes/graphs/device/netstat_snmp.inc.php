<?php

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/netstats-snmp.rrd";

$stats = array('snmpInTraps',
               'snmpOutTraps',
               'snmpInTotalReqVars',
               'snmpInTotalSetVars',
               'snmpOutGetResponses',
               'snmpOutSetRequests');

$i=0;
foreach ($stats as $stat)
{
  $i++;
  $rrd_list[$i]['filename'] = $rrd_filename;
  $rrd_list[$i]['descr'] = str_replace("snmp", "", $stat);
  $rrd_list[$i]['ds'] = $stat;
  if (strpos($stat, "Out") !== FALSE)
  {
    $rrd_list[$i]['invert'] = TRUE;
  }
}

$colours='mixed';

$scale_min = "0";
$nototal = 1;
$simple_rrd = TRUE;

include("includes/graphs/generic_multi.inc.php");

?>
