<?php

include("includes/graphs/common.inc.php");

$device = device_by_id_cache($id);

$rrd_options .= " -l 0 -E ";

$iter = "1";
$rrd_options .= " COMMENT:'Toner level            Cur     Min      Max\\n'";
foreach (dbFetchRows("SELECT * FROM toner where device_id = ?", array($id)) as $toner)
{
  $colour = toner2colour($toner['toner_descr']);

  if ($colour['left'] == NULL) 
  {
    # FIXME generic colour function
    switch ($iter)
    {
      case "1":
        $colour['left']= "000000";
        break;
      case "2":
        $colour['left']= "008C00";
        break;
      case "3":
        $colour['left']= "4096EE";
        break;
      case "4":
        $colour['left']= "73880A";
        break;
      case "5":
        $colour['left']= "D01F3C";
        break;
      case "6":
        $colour['left']= "36393D";
        break;
      case "7":
      default:
        $colour['left']= "FF0000";
        unset($iter);
        break;
    }
  }

  $hostname = gethostbyid($toner['device_id']);

  $descr = substr(str_pad($toner['toner_descr'], 16),0,16);
  $rrd_filename  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("toner-" . $toner['toner_index'] . ".rrd");
  $toner_id = $toner['toner_id'];

  $rrd_options .= " DEF:toner$toner_id=$rrd_filename:toner:AVERAGE";
  $rrd_options .= " LINE2:toner$toner_id#".$colour['left'].":'" . $descr . "'";
  $rrd_options .= " GPRINT:toner$toner_id:LAST:'%5.0lf%%'";
  $rrd_options .= " GPRINT:toner$toner_id:MIN:'%5.0lf%%'";
  $rrd_options .= " GPRINT:toner$toner_id:MAX:%5.0lf%%\\\\l";

  $iter++;
}

?>
