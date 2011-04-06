<?php

include("includes/graphs/common.inc.php");
$device = device_by_id_cache($id);

$rrd_options .= " -l 0 -E ";

$iter = "1";
$sql = mysql_query("SELECT * FROM sensors WHERE sensor_class='humidity' AND device_id = '$id' ORDER BY sensor_index");
$rrd_options .= " COMMENT:'                           Cur   Min   Max\\n'";
while ($humidity = mysql_fetch_assoc($sql))
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

  $humidity['sensor_descr_fixed'] = substr(str_pad($humidity['sensor_descr'], 22),0,22);
  $humidityrrd  = $config['rrd_dir'] . "/".$device['hostname']."/".safename("humidity-" . safename($humidity['sensor_descr']) . ".rrd");
  $rrd_options .= " DEF:sensor" . $humidity['sensor_id'] . "=$humidityrrd:sensor:AVERAGE ";
  $rrd_options .= " LINE1:sensor" . $humidity['sensor_id'] . "#" . $colour . ":'" . str_replace(':','\:',str_replace('\*','*',quotemeta($humidity['sensor_descr_fixed']))) . "' ";
  $rrd_options .= " GPRINT:sensor" . $humidity['sensor_id'] . ":LAST:%3.0lf%% ";
  $rrd_options .= " GPRINT:sensor" . $humidity['sensor_id'] . ":MIN:%3.0lf%% ";
  $rrd_options .= " GPRINT:sensor" . $humidity['sensor_id'] . ":MAX:%3.0lf%%\\\l ";
  $iter++;
}

?>