<?php

## Generate a list of ports and then call the multi_bits grapher to generate from the list

$i = 0;

while ($port = mysql_fetch_array($ports))
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
$rra_in  = "INOCTETS";
$rra_out = "OUTOCTETS";

include("includes/graphs/generic_multi_bits_separated.inc.php");

?>