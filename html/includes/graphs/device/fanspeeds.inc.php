<?php

include("includes/graphs/common.inc.php");

$device = device_by_id_cache($id);

$rrd_options .= " -l 0 -E ";

$iter = "1";
$sql = mysql_query("SELECT * FROM sensors WHERE sensor_class='fanspeed' AND device_id = '$id'");
$rrd_options .= " COMMENT:'RPM                    Cur     Min      Max\\n'";
while($fanspeed = mysql_fetch_array($sql)) 
{
  switch ($iter)
  {
    case "1":
      $colour= "CC0000"; 
      break; 
    case "2":
      $colour= "008C00"; 
      break; 
    case "3":
      $colour= "4096EE"; 
      break; 
    case "4":
      $colour= "73880A"; 
      break;
    case "5":
      $colour= "D01F3C"; 
      break;
    case "6":
      $colour= "36393D"; 
      break; 
    case "7": 
    default:
      $colour= "FF0084"; 
      unset($iter);
      break;
  }

  $descr = substr(str_pad($fanspeed['sensor_descr'], 17),0,17);
  $rrd_filename  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("fan-" . $fanspeed['sensor_descr'] . ".rrd");
  $fan_id = $fanspeed['sensor_id'];

  $rrd_options .= " DEF:fan$fan_id=$rrd_filename:fan:AVERAGE";
  $rrd_options .= " LINE1:fan$fan_id#".$colour.":'" . $descr . "'";
  $rrd_options .= " GPRINT:fan$fan_id:AVERAGE:%5.0lf\ ";
  $rrd_options .= " GPRINT:fan$fan_id:MIN:%5.0lf\ ";
  $rrd_options .= " GPRINT:fan$fan_id:MAX:%5.0lf\\\\l";

  $iter++;
}

?>
