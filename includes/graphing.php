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

function graph_global_bits ($graph, $from, $to, $width, $height) {

  global $rrdtool, $installdir, $mono_font, $rrd_dir;
  $imgfile = "graphs/" . $graph;
  $opts = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";

  $query = mysql_query("SELECT `ifIndex`, I.id as id, D.hostname FROM `interfaces` AS I, `devices` AS D WHERE I.host = D.id AND I.iftype LIKE '%ethernet%'");

  while($int = mysql_fetch_row($query)) {
    $hostname = $int[2];
    $id = $int[1];
    if(is_file($rrd_dir . "/" . $hostname . "." . $int[0] . ".rrd")) {
      $opts .= "DEF:inoctets" . $int[1] . "=" . $rrd_dir . "/" . $hostname . "." . $int[0] . ".rrd:INOCTETS:AVERAGE \
DEF:outoctets" . $int[1] . "=" . $rrd_dir . "/" . $hostname . "." . $int[0] . ".rrd:OUTOCTETS:AVERAGE \
";
                    $in_thing .= $seperator . "inoctets" . $int[1] . ",UN,0," . "inoctets" . $int[1] . ",IF";
                    $out_thing .= $seperator . "outoctets" . $int[1] . ",UN,0," . "outoctets" . $int[1] . ",IF";
                    $pluses .= $plus;
                    $seperator = ",";
                    $plus = ",+";
    }
  }

  $opts .= "     CDEF:inoctets=" . $in_thing . $pluses . " \
                 CDEF:outoctets=" . $out_thing . $pluses . " \
                 CDEF:doutoctets=outoctets,-1,*  \
                 CDEF:inbits=inoctets,8,*  \
                 CDEF:outbits=outoctets,8,*  \
                 CDEF:doutbits=doutoctets,8,*  \
                 AREA:inbits#CDEB8B: \
                 COMMENT:BPS    Current   Average      Max   95th %\\n \
                 LINE1.25:inbits#006600:In  \
                 GPRINT:inbits:LAST:%6.2lf%s \
                 GPRINT:inbits:AVERAGE:%6.2lf%s \ 
                 GPRINT:inbits:MAX:%6.2lf%s\l \
                 AREA:doutbits#C3D9FF: \
                 LINE1.25:doutbits#000099:Out \
                 GPRINT:outbits:LAST:%6.2lf%s \
                 GPRINT:outbits:AVERAGE:%6.2lf%s \
                 GPRINT:outbits:MAX:%6.2lf%s ";


  if($width <= '300') {
    $opts .= " --font LEGEND:7:$mono_font --font AXIS:6:$mono_font --font-render-mode normal ";
  }

  echo("<pre>");
  echo($imgfile . "\n" . $opts);


  $cmd = "/usr/bin/rrdtool";

  `rm /tmp/poo`;
  $handle = fopen("/tmp/poo", "w");
  fwrite($handle, " graph test.png " . $opts);

  if (( $fh = popen($cmd, 'r')) === false)
       die("Open failed : ${php_errormsg}\n");

  fwrite($fh, " graph $imgfile " . $opts);


  pclose($fh);

  

  echo`$rrdtool graph $imgfile $opts`;


#  $ret = rrd_graph("$imgfile", $opts, count($opts));

#  if( !is_array($ret) )
#  {
 #   $err = rrd_error();
#    echo "rrd_graph() ERROR: $err\n";
#  if ( !is_file($installdir . $imgfile) ) {
#    return FALSE;
#  } else {
    return $imgfile;
#  }

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

  $query = mysql_query("SELECT `ifIndex` FROM `interfaces` WHERE `host` = '$device' AND ifType LIKE '%ethernet%' OR `ifType` LIKE '%erial%'");

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
