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
  while($temperature = mysql_fetch_array($sql)) {
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }

    $temperature['temp_descr'] = str_pad($temperature['temp_descr'], 28);
    $temperature['temp_descr'] = substr($temperature['temp_descr'],0,28);

    $optsa[] = "DEF:temp" . $temperature[temp_id] . "=rrd/" . $hostname . "-temp-" . $temperature['temp_id'] . ".rrd:temp:AVERAGE";
    $optsa[] = "LINE1:temp" . $temperature[temp_id] . "#" . $colour . ":" . $temperature[temp_descr];
    $optsa[] = "GPRINT:temp" . $temperature[temp_id] . ":LAST:%3.0lf°C";
    $optsa[] = "GPRINT:temp" . $temperature[temp_id] . ":MAX:%3.0lf°C\l";
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

function graph_device_bits ($device, $graph, $from, $to, $width, $height)
{
  global $rrdtool, $installdir, $mono_font, $rrd_dir;
  $imgfile = "graphs/" . "$graph";
  $opts = array( "--alt-autoscale-max",
                 "-E",
                 "--start", $from, "--end", $to,
                 "--width", $width, "--height", $height);


  $hostname = gethostbyid($device);

  $query = mysql_query("SELECT `ifIndex` FROM `interfaces` WHERE `device_id` = '$device' AND `ifType` NOT LIKE '%oopback%' AND `ifType` NOT LIKE '%SVI%'");

  while($int = mysql_fetch_row($query)) {

    if(is_file($rrd_dir . "/" . $hostname . "." . $int[0] . ".rrd")) {
      $this_opts = array ("DEF:inoctets" . $int[0] . "=" . $rrd_dir . "/" . $hostname . "." . $int[0] . ".rrd:INOCTETS:AVERAGE",
                        "DEF:outoctets" . $int[0] . "=" . $rrd_dir . "/" . $hostname . "." . $int[0] . ".rrd:OUTOCTETS:AVERAGE");
                        $in_thing .= $seperator . "inoctets" . $int[0] . ",UN,0," . "inoctets" . $int[0] . ",IF";
                        $out_thing .= $seperator . "outoctets" . $int[0] . ",UN,0," . "outoctets" . $int[0] . ",IF";
			$pluses .= $plus;
                        $seperator = ",";
			$plus = ",+";
      $opts = array_merge($opts, $this_opts);
    }
  }

  $opts_end = array(
                 "CDEF:inoctets=" . $in_thing . $pluses,
                 "CDEF:outoctets=" . $out_thing . $pluses, 
                 "CDEF:doutoctets=outoctets,-1,*",
                 "CDEF:inbits=inoctets,8,*",
                 "CDEF:outbits=outoctets,8,*",
                 "CDEF:doutbits=doutoctets,8,*",
                 "AREA:inbits#CDEB8B:",
                 "COMMENT:BPS    Current   Average      Max\\n",
                 "LINE1.25:inbits#006600:In ",
                 "GPRINT:inbits:LAST:%6.2lf%s",
                 "GPRINT:inbits:AVERAGE:%6.2lf%s",
                 "GPRINT:inbits:MAX:%6.2lf%s\l",
                 "AREA:doutbits#C3D9FF:",
                 "LINE1.25:doutbits#000099:Out",
                 "GPRINT:outbits:LAST:%6.2lf%s",
                 "GPRINT:outbits:AVERAGE:%6.2lf%s",
                 "GPRINT:outbits:MAX:%6.2lf%s",
                 );

  $opts = array_merge($opts, $opts_end);

  if($width <= "300") {
    $this_opts = array("--font", "LEGEND:7:$mono_font",
                      "--font", "AXIS:6:$mono_font",
                      "--font-render-mode", "normal");

    $opts = array_merge($opts, $this_opts);

  }

#  echo("<pre>");
#  print_r ($opts);

  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) )
  {
    $err = rrd_error();
    echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}


?>
