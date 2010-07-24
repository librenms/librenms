<?php

include("includes/graphs/common.inc.php");
$device = device_by_id_cache($id);

$rrd_options .= " -l 0 -E ";

$iter = "1";
$sql = mysql_query("SELECT * FROM frequency where device_id = '$id'");
$rrd_options .= " COMMENT:'                       Cur     Min      Max\\n'";
while($frequency = mysql_fetch_array($sql)) 
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

  $hostname = gethostbyid($frequency['device_id']);
  
  $descr = substr(str_pad($frequency['freq_descr'], 15),0,15);
  $rrd_filename  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("freq-" . $frequency['freq_descr'] . ".rrd");
  $freq_id = $frequency['freq_id'];

  $rrd_options .= " DEF:freq$freq_id=$rrd_filename:freq:AVERAGE";
  $rrd_options .= " LINE1:freq$freq_id#".$colour.":'" . $descr . "'";
  $rrd_options .= " GPRINT:freq$freq_id:AVERAGE:%5.2lfHz";
  $rrd_options .= " GPRINT:freq$freq_id:MIN:%5.2lfHz";
  $rrd_options .= " GPRINT:freq$freq_id:MAX:%5.2lfHz\\\\l";

  $iter++;
}


?>
