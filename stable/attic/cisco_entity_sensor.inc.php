<?php

$sensor = mysql_fetch_assoc(mysql_query("SELECT * FROM entPhysical as E, devices as D WHERE entPhysical_id = '".mres($_GET['a'])."' and D.device_id = E.device_id"));

switch ($sensor['entSensorType'])
{
  case 'celsius':
  case 'watts':
  case 'voltsDC':
  case 'dBm':
  case 'amperes':
    $scale_min = "0";
    break;
}

include("includes/graphs/common.inc.php");

$rrd_filename = $config['rrd_dir'] . "/" . $sensor['hostname'] . "/" . safename("ces-" . $sensor['entPhysicalIndex'] . ".rrd");

$type = str_pad($sensor['entSensorType'], 8);
$type = substr($type,0,8);

$rrd_options .= " DEF:avg=$rrd_filename:value:AVERAGE";
$rrd_options .= " DEF:min=$rrd_filename:value:MIN";
$rrd_options .= " DEF:max=$rrd_filename:value:MAX";
$rrd_options .= " COMMENT:'             Last     Min     Max      Ave\\n'";
$rrd_options .= " AREA:max#a5a5a5";
$rrd_options .= " AREA:min#ffffff";
$rrd_options .= " LINE1.25:avg#aa2200:'".$type."'";
$rrd_options .= " GPRINT:avg:AVERAGE:%5.2lf%s";
$rrd_options .= " GPRINT:max:MAX:%5.2lf%s";
$rrd_options .= " GPRINT:max:MAX:%5.2lf%s";
$rrd_options .= " GPRINT:avg:LAST:%5.2lf%s";

?>
