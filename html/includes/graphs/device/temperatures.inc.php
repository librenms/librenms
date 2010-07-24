<?php

include("includes/graphs/common.inc.php");
$device = device_by_id_cache($id);

$rrd_options .= " -l 0 -E ";

$iter = "1";
$sql = mysql_query("SELECT * FROM sensors WHERE sensor_class='temperature' AND device_id = '$id' ORDER BY sensor_index");
$rrd_options .= " COMMENT:'                           Cur   Min   Max\\n'";
while($temperature = mysql_fetch_array($sql)) 
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
  
  $temperature['sensor_descr_fixed'] = substr(str_pad($temperature['sensor_descr'], 22),0,22);
  $temprrd  = $config['rrd_dir'] . "/".$device['hostname']."/".safename("temp-" . $temperature['sensor_descr'] . ".rrd");
  $rrd_options .= " DEF:temp" . $temperature['sensor_id'] . "=$temprrd:temp:AVERAGE ";
  $rrd_options .= " LINE1:temp" . $temperature['sensor_id'] . "#" . $colour . ":'" . str_replace(':','\:',str_replace('\*','*',quotemeta($temperature['sensor_descr_fixed']))) . "' ";
  $rrd_options .= " GPRINT:temp" . $temperature['sensor_id'] . ":LAST:%3.0lfC ";
  $rrd_options .= " GPRINT:temp" . $temperature['sensor_id'] . ":MIN:%3.0lfC ";
  $rrd_options .= " GPRINT:temp" . $temperature['sensor_id'] . ":MAX:%3.0lfC\\\l ";
  $iter++;
}


?>
