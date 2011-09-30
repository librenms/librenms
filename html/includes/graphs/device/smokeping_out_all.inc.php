<?php

$i=0;

include("smokeping_common.inc.php");

foreach($smokeping_files['out'][$device['hostname']] as $source => $filename)
{
  $i++;
  $rrd_list[$i]['filename'] = $config['smokeping']['dir'] . $filename;
  $rrd_list[$i]['descr'] = $source;
  $rrd_list[$i]['ds'] = "median";
}

$colours='mixed';

$nototal = 1;
$simple_rrd = 1;

include("includes/graphs/generic_multi_line.inc.php");

?>
