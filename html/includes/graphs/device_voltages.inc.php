<?php

include("common.inc.php");

$rrd_options .= " -l 0 -E ";

$iter = "1";
$sql = mysql_query("SELECT * FROM voltage where device_id = '$device_id'");
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
  
  $descr = substr(str_pad($voltage['volt_descr'], 17),0,17);
  $rrd_filename  = $config['rrd_dir'] . "/".$hostname."/" . safename("volt-" . $voltage['volt_descr'] . ".rrd");
  $volt_id = $voltage['volt_id'];

  $rrd_options .= " DEF:volt$volt_id=$rrd_filename:volt:AVERAGE";
  $rrd_options .= " LINE1:volt$volt_id#".$colour.":'" . $descr . "'";
  $rrd_options .= " GPRINT:volt$volt_id:AVERAGE:%5.2lfV";
  $rrd_options .= " GPRINT:volt$volt_id:MIN:%5.2lfV";
  $rrd_options .= " GPRINT:volt$volt_id:MAX:%5.2lfV\\\\l";

  $iter++;
}


?>
