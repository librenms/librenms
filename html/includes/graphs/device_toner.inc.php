<?php

include("common.inc.php");

$rrd_options .= " -l 0 -E ";

$iter = "1";
$sql = mysql_query("SELECT * FROM toner where device_id = '$device_id'");
$rrd_options .= " COMMENT:'RPM                    Cur     Min      Max\\n'";
while($toner = mysql_fetch_array($sql)) 
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

  $hostname = gethostbyid($toner['device_id']);
  
  $descr = substr(str_pad($toner['toner_descr'], 17),0,17);
  $rrd_filename  = $config['rrd_dir'] . "/".$hostname."/" . safename("toner-" . $toner['toner_descr'] . ".rrd");
  $toner_id = $toner['toner_id'];

  $rrd_options .= " DEF:toner$toner_id=$rrd_filename:toner:AVERAGE";
  $rrd_options .= " LINE1:toner$toner_id#".$colour.":'" . $descr . "'";
  $rrd_options .= " GPRINT:toner$toner_id:AVERAGE:%5.0lf\ ";
  $rrd_options .= " GPRINT:toner$toner_id:MIN:%5.0lf\ ";
  $rrd_options .= " GPRINT:toner$toner_id:MAX:%5.0lf\\\\l";

  $iter++;
}


?>
