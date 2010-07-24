<?php

include("includes/graphs/common.inc.php");
$device = device_by_id_cache($id);

$rrd_options .= " -l 0 -E ";

$iter = "1";
$sql = mysql_query("SELECT * FROM sensors WHERE sensor_class='voltage' AND device_id = '$id'");
$rrd_options .= " COMMENT:'                       Cur     Min      Max\\n'";
while($voltage = mysql_fetch_array($sql)) 
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

  $hostname = gethostbyid($voltage['device_id']);
  
  $descr = substr(str_pad($voltage['sensor_descr'], 15),0,15);
  $rrd_filename  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("volt-" . $voltage['sensor_descr'] . ".rrd");
  $volt_id = $voltage['sensor_id'];

  $rrd_options .= " DEF:volt$volt_id=$rrd_filename:volt:AVERAGE";
  $rrd_options .= " LINE1:volt$volt_id#".$colour.":'" . $descr . "'";
  $rrd_options .= " GPRINT:volt$volt_id:AVERAGE:%5.2lfV";
  $rrd_options .= " GPRINT:volt$volt_id:MIN:%5.2lfV";
  $rrd_options .= " GPRINT:volt$volt_id:MAX:%5.2lfV\\\\l";

  $iter++;
}


?>
