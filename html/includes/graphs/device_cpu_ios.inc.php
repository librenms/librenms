<?php

$query = mysql_query("SELECT * FROM `cpmCPU` where `device_id` = '".mres($device_id)."'");

$i=0;
while($proc = mysql_fetch_array($query)) {
  $rrd_filename  = $config['rrd_dir'] . "/$hostname/" . safename("cpmCPU-" . $proc['cpmCPU_oid'] . ".rrd");
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

if($rrd_list) {include ("generic_multi_line.inc.php"); } else {
  include("common.inc.php");
  $database = $config['rrd_dir'] . "/" . $hostname . "/ios-cpu.rrd";
  $rrd_options .= " DEF:5m=$database:LOAD5M:AVERAGE";
  $rrd_options .= " DEF:5m_max=$database:LOAD5M:MAX";
  $rrd_options .= " DEF:5m_min=$database:LOAD5M:MIN";
  $rrd_options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ Current\ \ Minimum\ \ Maximum\ \ Average\\\\n";
  $rrd_options .= " AREA:5m#ffee99: LINE1.25:5m#aa2200:Load\ %";
  $rrd_options .= " GPRINT:5m:LAST:%6.2lf\  GPRINT:5m_min:AVERAGE:%6.2lf\ ";
  $rrd_options .= " GPRINT:5m_max:MAX:%6.2lf\  GPRINT:5m:AVERAGE:%6.2lf\\\\n";
}

?>
