<?php

include("common.inc.php");
$device = device_by_id_cache($id);

$rrd_options .= " -l 0 -E ";

$iter = "1";
$sql = mysql_query("SELECT * FROM current where device_id = '$id'");
$rrd_options .= " COMMENT:'                       Cur     Min      Max\\n'";
while($current = mysql_fetch_array($sql)) 
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

  $hostname = gethostbyid($current['device_id']);
  
  $descr = substr(str_pad($current['current_descr'], 15),0,15);
  $rrd_filename  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("current-" . $current['current_descr'] . ".rrd");
  $current_id = $current['current_id'];

  $rrd_options .= " DEF:current$current_id=$rrd_filename:current:AVERAGE";
  $rrd_options .= " LINE1:current$current_id#".$colour.":'" . $descr . "'";
  $rrd_options .= " GPRINT:current$current_id:AVERAGE:%5.2lfA";
  $rrd_options .= " GPRINT:current$current_id:MIN:%5.2lfA";
  $rrd_options .= " GPRINT:current$current_id:MAX:%5.2lfA\\\\l";

  $iter++;
}


?>
