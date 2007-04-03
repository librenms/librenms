<?php

function temp_graph ($device, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--vertical-label", $vertical, "--alt-autoscale-max",
                 "-l 0",
                 "-E",
                 "-b 1024",
                 "--title", $title);
  $hostname = gethostbyid($device);
  $imgfile = "graphs/" . "$graph";
  $iter = "1";
  $sql = mysql_query("SELECT * FROM temperature where temp_host = '$device'");
  $optsa[] = "COMMENT:                                  Cur    Max";
  while($fs = mysql_fetch_array($sql)) {
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }

    $fs[temp_descr] = str_pad($fs[temp_descr], 28);
    $fs[temp_descr] = substr($fs[temp_descr],0,28);

    $optsa[] = "DEF:temp$fs[temp_id]=rrd/$hostname-temp$fs[temp_id].rrd:temp:AVERAGE";
    $optsa[] = "LINE1:temp$fs[temp_id]#" . $colour . ":$fs[temp_descr]";
    $optsa[] = "GPRINT:temp$fs[temp_id]:LAST:%3.0lf°C";
    $optsa[] = "GPRINT:temp$fs[temp_id]:MAX:%3.0lf°C\l";
    $iter++;
 }
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error();
    echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}

?>
