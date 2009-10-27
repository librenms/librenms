<?php

$query = mysql_query("SELECT * FROM `cpmCPU` where `device_id` = '".mres($device_id)."'");

$i=0;
while($proc = mysql_fetch_array($query)) {

  $rrd_filename  = $config['rrd_dir'] . "/$hostname/cpmCPU-" . $proc['cpmCPU_oid'] . ".rrd";

  if(is_file($rrd_filename)) {

    $descr = str_pad($proc['entPhysicalDescr'], 8);
    $descr = substr($descr,0,8);

    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['rra'] = "usage";
    $i++;
  }
}

$unit_text = "Load %";

$units='%';
$total_units='%';
$colours='mixed';

$scale_min = "0";
$scale_max = "100";

$nototal = 1;

include ("generic_multi_line.inc.php");


?>
