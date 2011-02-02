<?php

include("includes/graphs/common.inc.php");
$device = device_by_id_cache($id);

if($_GET['width'] > "300") { $descr_len = "40"; } else { $descr_len = "22"; }

$rrd_options .= " -l 0 -E ";
$iter = "1";
$sql = mysql_query("SELECT * FROM sensors WHERE sensor_class='temperature' AND device_id = '$id' ORDER BY sensor_index");
$rrd_options .= " COMMENT:'".str_pad('',$descr_len)."    Cur     Min    Max\\n'";
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
  
  $temperature['sensor_descr_fixed'] = substr(str_pad($temperature['sensor_descr'], $descr_len),0,$descr_len);
  $rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/temperature-" . safename($temperature['sensor_type']."-".$temperature['sensor_index']) . ".rrd";
  $rrd_options .= " DEF:sensor" . $temperature['sensor_id'] . "=$rrd_file:sensor:AVERAGE ";
  $rrd_options .= " LINE1:sensor" . $temperature['sensor_id'] . "#" . $colour . ":'" . str_replace(':','\:',str_replace('\*','*',$temperature['sensor_descr_fixed'])) . "'";
  $rrd_options .= " GPRINT:sensor" . $temperature['sensor_id'] . ":LAST:%4.1lfC ";
  $rrd_options .= " GPRINT:sensor" . $temperature['sensor_id'] . ":MIN:%4.1lfC ";
  $rrd_options .= " GPRINT:sensor" . $temperature['sensor_id'] . ":MAX:%4.1lfC\\\l ";
  $iter++;
}


?>
