<?php

include("includes/graphs/common.inc.php");

$device = device_by_id_cache($id);

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/netstats-snmp.rrd";

$stats = array('snmpInTraps',
               'snmpOutTraps',
               'snmpInTotalReqVars',
               'snmpInTotalSetVars',
               'snmpOutGetResponses',
               'snmpOutSetRequests');

$i=0;
foreach($stats as $stat)
{
  $i++;
  $rrd_list[$i]['filename'] = $rrd_filename;
  $rrd_list[$i]['descr'] = str_replace("snmp", "", $stat);
  $rrd_list[$i]['rra'] = $stat;
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
