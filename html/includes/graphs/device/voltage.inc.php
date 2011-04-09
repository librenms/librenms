<?php

include("includes/graphs/common.inc.php");
$device = device_by_id_cache($id);

$rrd_options .= " -l 0 -E ";

if ($_GET['width'] > "300") { $descr_len = "38"; } else { $descr_len = "18"; }
$rrd_options .= " COMMENT:'".str_pad('',$descr_len)."     Cur      Min     Max\\n'";

$iter = "1";
$sql = mysql_query("SELECT * FROM sensors WHERE sensor_class='voltage' AND device_id = '$id'");
while ($sensor = mysql_fetch_assoc($sql))
{
  # FIXME generic colour function
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

  $sensor['sensor_descr_fixed'] = substr(str_pad($sensor['sensor_descr'], $descr_len),0,$descr_len);
  $sensor['sensor_descr_fixed'] = str_replace(':','\:',str_replace('\*','*',$sensor['sensor_descr_fixed']));

  $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/voltage-" . safename($sensor['sensor_type']."-".$sensor['sensor_index']) . ".rrd";

  $rrd_options .= " DEF:sensor" . $sensor['sensor_id'] . "=$rrd_filename:sensor:AVERAGE";
  $rrd_options .= " LINE1:sensor" . $sensor['sensor_id'] . "#".$colour.":'" . $sensor['sensor_descr_fixed'] . "'";
  $rrd_options .= " GPRINT:sensor" . $sensor['sensor_id'] . ":AVERAGE:%5.2lfV";
  $rrd_options .= " GPRINT:sensor" . $sensor['sensor_id'] . ":MIN:%5.2lfV";
  $rrd_options .= " GPRINT:sensor" . $sensor['sensor_id'] . ":MAX:%5.2lfV\\\\l";

  $iter++;
}

?>