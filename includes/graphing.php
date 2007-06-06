<?php

function temp_graph ($temp, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--vertical-label", $vertical, "--alt-autoscale-max",
                 "-l 0",
                 "-E",
                 "-b 1024",
                 "--title", $title);
  $hostname = gethostbyid($device);
  $imgfile = "graphs/" . "$graph";
  $iter = "1";
  $sql = mysql_query("SELECT * FROM temperature where temp_id = '$temp'");
  $optsa[] = "COMMENT:                                  Cur    Max";
  while($temperature = mysql_fetch_array($sql)) {
    $hostname = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '" . $temperature['temp_host'] . "'"),0);
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }

    $temperature['temp_descr_fixed'] = str_pad($temperature['temp_descr'], 28);
    $temperature['temp_descr_fixed'] = substr($temperature['temp_descr_fixed'],0,28);

    $temprrd  = addslashes("rrd/$hostname-temp-" . str_replace("/", "_", str_replace(" ", "_",$temperature['temp_descr'])) . ".rrd");
    $temprrd  = str_replace(")", "_", $temprrd);
    $temprrd  = str_replace("(", "_", $temprrd);



    $optsa[] = "DEF:temp" . $temperature[temp_id] . "=$temprrd:temp:AVERAGE";
    $optsa[] = "AREA:temp" . $temperature[temp_id] . "#ffcccc:" . $temperature[temp_descr_fixed];
    $optsa[] = "LINE1.5:temp" . $temperature[temp_id] . "#" . $colour . ":" . $temperature[temp_descr_fixed];
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


function temp_graph_dev ($device, $graph, $from, $to, $width, $height, $title, $vertical) {
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

    $temperature['temp_descr_fixed'] = str_pad($temperature['temp_descr'], 28);
    $temperature['temp_descr_fixed'] = substr($temperature['temp_descr_fixed'],0,28);

    $temprrd  = addslashes("rrd/$hostname-temp-" . str_replace("/", "_", str_replace(" ", "_",$temperature['temp_descr'])) . ".rrd");
    $temprrd  = str_replace(")", "_", $temprrd);
    $temprrd  = str_replace("(", "_", $temprrd);



    $optsa[] = "DEF:temp" . $temperature[temp_id] . "=$temprrd:temp:AVERAGE";
    $optsa[] = "LINE1:temp" . $temperature[temp_id] . "#" . $colour . ":" . $temperature[temp_descr_fixed];
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

function trafgraph ($rrd, $graph, $from, $to, $width, $height)
{
  global $rrdtool, $installdir, $mono_font;    
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--alt-autoscale-max",
		 "-E", 
                 "--start", $from, "--end", $to, 
                 "--width", $width, "--height", $height, 
                 "DEF:inoctets=$database:INOCTETS:AVERAGE",
                 "DEF:outoctets=$database:OUTOCTETS:AVERAGE",
                 "CDEF:doutoctets=outoctets,-1,*",
                 "CDEF:inbits=inoctets,8,*",
                 "CDEF:outbits=outoctets,8,*",
                 "CDEF:doutbits=doutoctets,8,*",
		 "VDEF:95thin=inbits,95,PERCENT",
		 "VDEF:95thout=outbits,95,PERCENT",
	         "VDEF:d95thout=doutbits,5,PERCENT",
                 "AREA:inbits#CDEB8B:",
                 "COMMENT:BPS    Current   Average      Max   95th %\\n",
                 "LINE1.25:inbits#006600:In ",
                 "GPRINT:inbits:LAST:%6.2lf%s",
                 "GPRINT:inbits:AVERAGE:%6.2lf%s",
                 "GPRINT:inbits:MAX:%6.2lf%s",
                 "GPRINT:95thin:%6.2lf%s\\n",
                 "AREA:doutbits#C3D9FF:",
                 "LINE1.25:doutbits#000099:Out",
                 "GPRINT:outbits:LAST:%6.2lf%s",
                 "GPRINT:outbits:AVERAGE:%6.2lf%s",
                 "GPRINT:outbits:MAX:%6.2lf%s",
                 "GPRINT:95thout:%6.2lf%s",
                 "LINE1:95thin#aa0000",
                 "LINE1:d95thout#aa0000:" );
  

  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);

  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) )
  {
    $err = rrd_error();
  #  echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}

function pktsgraph ($rrd, $graph, $from, $to, $width, $height) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--alt-autoscale-max",
                 "-l 0",
                 "-E", 
                 "--start", $from, "--end", $to,
                 "--width", $width, "--height", $height,
                 "DEF:in=$database:INUCASTPKTS:AVERAGE",
                 "DEF:out=$database:OUTUCASTPKTS:AVERAGE",
                 "CDEF:dout=out,-1,*",
                 "AREA:in#aa66aa:",
                 "COMMENT:Packets    Current     Average      Maximum\\n",
                 "LINE1.25:in#330033:In  ",
                 "GPRINT:in:LAST:%6.2lf%spps",
                 "GPRINT:in:AVERAGE:%6.2lf%spps",
                 "GPRINT:in:MAX:%6.2lf%spps\\n",
                 "AREA:dout#FFDD88:",
                 "LINE1.25:dout#FF6600:Out ",
		 "GPRINT:out:LAST:%6.2lf%spps",
                 "GPRINT:out:AVERAGE:%6.2lf%spps",
                 "GPRINT:out:MAX:%6.2lf%spps\\n");
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);
  
  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) ) {
    $err = rrd_error();
#    echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}

function errorgraph ($rrd, $graph, $from, $to, $width, $height)
{
    global $rrdtool, $installdir, $mono_font;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";

    $optsa = array( 
                   "--alt-autoscale-max",
                   "-E", 
                   "-l 0",
                   "--start", $from, "--end", $to,
                   "--width", $width, "--height", $height,
                   "DEF:in=$database:INERRORS:AVERAGE",
                   "DEF:out=$database:OUTERRORS:AVERAGE",
                   "CDEF:dout=out,-1,*",
		   "AREA:in#ff3300:",
                   "COMMENT:Errors    Current     Average      Maximum\\n",
                   "LINE1.25:in#ff0000:In ",
                   "GPRINT:in:LAST:%6.2lf%spps",
                   "GPRINT:in:AVERAGE:%6.2lf%spps",
                   "GPRINT:in:MAX:%6.2lf%spps\\n",
                   "AREA:dout#ff6633:",
                   "LINE1.25:out#cc3300:Out",
                   "GPRINT:out:LAST:%6.2lf%spps",
                   "GPRINT:out:AVERAGE:%6.2lf%spps",
                   "GPRINT:out:MAX:%6.2lf%spps\\n",
                   );
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);

    $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error();
 #   echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }

}

function nucastgraph ($rrd, $graph, $from, $to, $width, $height)
{
    global $rrdtool, $installdir, $mono_font;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to,
                 "--width", $width, "--height", $height,
                 "--alt-autoscale-max",
                 "-E", 
                 "-l 0",
                 "DEF:in=$database:INNUCASTPKTS:AVERAGE",
                 "DEF:out=$database:OUTNUCASTPKTS:AVERAGE",
                 "CDEF:dout=out,-1,*",
                 "AREA:in#aa66aa:",
                 "COMMENT:Packets     Current     Average      Maximum\\n",
                 "LINE1.25:in#330033:In   ",
                 "GPRINT:in:LAST:%6.2lf%spps",
                 "GPRINT:in:AVERAGE:%6.2lf%spps",
                 "GPRINT:in:MAX:%6.2lf%spps\\n",
                 "AREA:dout#FFDD88:",
                 "LINE1.25:dout#FF6600:Out  ",
                 "GPRINT:out:LAST:%6.2lf%spps",
                 "GPRINT:out:AVERAGE:%6.2lf%spps",
                 "GPRINT:out:MAX:%6.2lf%spps\\n");
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

function cpugraph ($rrd, $graph , $from, $to, $width, $height)
{
 global $rrdtool, $installdir, $mono_font;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
    $optsa = array( "--start", $from, "--width", $width, "--height", $height, "--vertical-label", $vertical, "--alt-autoscale-max",
                 "-l 0",
                 "-E", 
                 "--title", $title,
                 "DEF:5s=$database:LOAD5S:AVERAGE",
                 "DEF:5m=$database:LOAD5M:AVERAGE",
                 "COMMENT: Days     Current  Minimum  Maximum  Average\\n",
                 "AREA:5m#c5aa00:",
                 "AREA:5s#ffeeaa:5 sec",
                 "LINE1:5s#ea8f00:",
                 "GPRINT:5s:LAST:%6.2lf ",
                 "GPRINT:5s:AVERAGE:%6.2lf ",
                 "GPRINT:5s:MAX:%6.2lf ",
                 "GPRINT:5s:AVERAGE:%6.2lf\\n",
                 "LINE1.25:5m#aa2200:5min",
                 "GPRINT:5m:LAST:%6.2lf ",
                 "GPRINT:5m:AVERAGE:%6.2lf ",
                 "GPRINT:5m:MAX:%6.2lf ",
                 "GPRINT:5m:AVERAGE:%6.2lf\\n");

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

function uptimegraph ($rrd, $graph , $from, $to, $width, $height, $title, $vertical)
{
 global $rrdtool, $installdir, $mono_font;
    $rrd = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
    $optsa = array( "--start", $from, "--width", $width, "--height", $height, "--alt-autoscale-max",
                   "-E",  "-l 0",
            "DEF:uptime=$rrd:uptime:AVERAGE",
            "CDEF:cuptime=uptime,86400,/",
            "COMMENT: Days     Current  Minimum  Maximum  Average\\n",
            "AREA:cuptime#EEEEEE:Uptime",
            "LINE1.25:cuptime#36393D:",
            "GPRINT:cuptime:LAST:%6.2lf ",
            "GPRINT:cuptime:AVERAGE:%6.2lf ",
            "GPRINT:cuptime:MAX:%6.2lf ",
            "GPRINT:cuptime:AVERAGE:%6.2lf\\n");
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);

  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) ) {
    $err = rrd_error();
#    echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}


function memgraph ($rrd, $graph , $from, $to, $width, $height, $title, $vertical)
{
 global $rrdtool, $installdir, $mono_font;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
    $memrrd = $database;
    $opts = "--start $from \
            --alt-autoscale-max \
            --width $width --height $height \
            -l 0 -E \
            -b 1024 \
             DEF:MEMTOTAL=$memrrd:MEMTOTAL:AVERAGE \
             DEF:IOFREE=$memrrd:IOFREE:AVERAGE \
             DEF:IOUSED=$memrrd:IOUSED:AVERAGE \
             DEF:PROCFREE=$memrrd:PROCFREE:AVERAGE \
             DEF:PROCUSED=$memrrd:PROCUSED:AVERAGE \
	     CDEF:FREE=IOFREE,PROCFREE,+ \
             CDEF:USED=IOUSED,PROCUSED,+ \
             COMMENT:'Bytes    Current  Minimum  Maximum  Average\\n' \
             AREA:USED#f0e0a0:Used\
             GPRINT:USED:LAST:\%6.2lf%s\
             GPRINT:USED:MIN:%6.2lf%s\
	     GPRINT:USED:MAX:%6.2lf%s\
             GPRINT:USED:AVERAGE:'%6.2lf%s \\n'\
             AREA:FREE#cccccc:Free:STACK\
             GPRINT:FREE:LAST:\%6.2lf%s\
             GPRINT:FREE:MIN:%6.2lf%s\
             GPRINT:FREE:MAX:%6.2lf%s\
             GPRINT:FREE:AVERAGE:%6.2lf%s\
             LINE1:USED#d0b080:\
             LINE1:MEMTOTAL#000000:";

  if($width <= "300") {$opts .= "\
                                 --font LEGEND:7:$mono_font \
                                 --font AXIS:6:$mono_font \
                                 --font-render-mode normal";}


    `$rrdtool graph $imgfile $opts`;
    return $imgfile;
}

function ip_graph ($rrd, $graph, $from, $to, $width, $height) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--alt-autoscale-max", "-E", "-l 0",
                 "DEF:ipForwDatagrams=$database:ipForwDatagrams:AVERAGE",
                 "DEF:ipInDelivers=$database:ipInDelivers:AVERAGE",
                 "DEF:ipInReceives=$database:ipInReceives:AVERAGE",
                 "DEF:ipOutRequests=$database:ipOutRequests:AVERAGE",
                 "DEF:ipInDiscards=$database:ipInDiscards:AVERAGE",
                 "DEF:ipOutDiscards=$database:ipOutDiscards:AVERAGE",
                 "DEF:ipOutNoRoutes=$database:ipInDiscards:AVERAGE",
                 "COMMENT:Packets/sec    Current    Average   Maximum\\n",
                 "LINE1.25:ipForwDatagrams#cc0000:ForwDgrams ",
                 "GPRINT:ipForwDatagrams:LAST:%6.2lf%s",
                 "GPRINT:ipForwDatagrams:AVERAGE: %6.2lf%s",
                 "GPRINT:ipForwDatagrams:MAX: %6.2lf%s\\n",
                 "LINE1.25:ipInDelivers#00cc00:InDelivers ",
                 "GPRINT:ipInDelivers:LAST:%6.2lf%s",
                 "GPRINT:ipInDelivers:AVERAGE: %6.2lf%s",
                 "GPRINT:ipInDelivers:MAX: %6.2lf%s\\n",
                 "LINE1.25:ipInReceives#006600:InReceives ",
                 "GPRINT:ipInReceives:LAST:%6.2lf%s",
                 "GPRINT:ipInReceives:AVERAGE: %6.2lf%s",
                 "GPRINT:ipInReceives:MAX: %6.2lf%s\\n",
                 "LINE1.25:ipOutRequests#0000cc:OutRequests",
                 "GPRINT:ipOutRequests:LAST:%6.2lf%s",
                 "GPRINT:ipOutRequests:AVERAGE: %6.2lf%s",
                 "GPRINT:ipOutRequests:MAX: %6.2lf%s\\n",
                 "LINE1.25:ipInDiscards#cccc00:InDiscards ",
                 "GPRINT:ipInDiscards:LAST:%6.2lf%s",
                 "GPRINT:ipInDiscards:AVERAGE: %6.2lf%s",
                 "GPRINT:ipInDiscards:MAX: %6.2lf%s\\n",
                 "LINE1.25:ipOutDiscards#330033:OutDiscards",
                 "GPRINT:ipOutDiscards:LAST:%6.2lf%s",
                 "GPRINT:ipOutDiscards:AVERAGE: %6.2lf%s",
                 "GPRINT:ipOutDiscards:MAX: %6.2lf%s\\n",
                 "LINE1.25:ipOutNoRoutes#660000:OutNoRoutes",
                 "GPRINT:ipOutNoRoutes:LAST:%6.2lf%s",
                 "GPRINT:ipOutNoRoutes:AVERAGE: %6.2lf%s",
                 "GPRINT:ipOutNoRoutes:MAX: %6.2lf%s\\n"
		 );
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font", "--font", "AXIS:6:$mono_font", "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) { 
    $err = rrd_error(); echo "rrd_graph() ERROR: $err\n"; return FALSE;
  } else {
    return $imgfile;
  }
}

function icmp_graph ($rrd, $graph, $from, $to, $width, $height) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--alt-autoscale-max", "-E", "-l 0",
                "DEF:icmpInMsgs=$database:icmpInMsgs:AVERAGE",
                 "DEF:icmpOutMsgs=$database:icmpOutMsgs:AVERAGE",
                 "DEF:icmpInErrors=$database:icmpInErrors:AVERAGE",
                 "DEF:icmpOutErrors=$database:icmpOutErrors:AVERAGE",
                 "DEF:icmpInEchos=$database:icmpInEchos:AVERAGE",
                 "DEF:icmpOutEchos=$database:icmpOutEchos:AVERAGE",
                 "DEF:icmpInEchoReps=$database:icmpInEchoReps:AVERAGE",
                 "DEF:icmpOutEchoReps=$database:icmpOutEchoReps:AVERAGE",
                 "COMMENT:Packets/sec    Current    Average   Maximum\\n",
                 "LINE1.25:icmpInMsgs#00cc00:InMsgs     ",
                 "GPRINT:icmpInMsgs:LAST:%6.2lf%s",
                 "GPRINT:icmpInMsgs:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpInMsgs:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpOutMsgs#006600:OutMsgs    ",
                 "GPRINT:icmpOutMsgs:LAST:%6.2lf%s",
                 "GPRINT:icmpOutMsgs:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpOutMsgs:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpInErrors#cc0000:InErrors   ",
                 "GPRINT:icmpInErrors:LAST:%6.2lf%s",
                 "GPRINT:icmpInErrors:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpInErrors:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpOutErrors#660000:OutErrors  ",
                 "GPRINT:icmpOutErrors:LAST:%6.2lf%s",
                 "GPRINT:icmpOutErrors:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpOutErrors:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpInEchos#0066cc:InEchos    ",
                 "GPRINT:icmpInEchos:LAST:%6.2lf%s",
                 "GPRINT:icmpInEchos:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpInEchos:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpOutEchos#003399:OutEchos   ",
                 "GPRINT:icmpOutEchos:LAST:%6.2lf%s",
                 "GPRINT:icmpOutEchos:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpOutEchos:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpInEchoReps#cc00cc:InEchoReps ",
                 "GPRINT:icmpInEchoReps:LAST:%6.2lf%s",
                 "GPRINT:icmpInEchoReps:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpInEchoReps:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpOutEchoReps#990099:OutEchoReps",
                 "GPRINT:icmpOutEchoReps:LAST:%6.2lf%s",
                 "GPRINT:icmpOutEchoReps:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpOutEchoReps:MAX: %6.2lf%s\\n"
                 );
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font", "--font", "AXIS:6:$mono_font", "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error(); echo "rrd_graph() ERROR: $err\n"; return FALSE;
  } else {
    return $imgfile;
  }
}

function tcp_graph ($rrd, $graph, $from, $to, $width, $height) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--alt-autoscale-max", "-E", "-l 0",
                 "DEF:tcpActiveOpens=$database:tcpActiveOpens:AVERAGE",
                 "DEF:tcpPassiveOpens=$database:tcpPassiveOpens:AVERAGE",
                 "DEF:tcpAttemptFails=$database:tcpAttemptFails:AVERAGE",
                 "DEF:tcpEstabResets=$database:tcpEstabResets:AVERAGE",
                 "DEF:tcpInSegs=$database:tcpInSegs:AVERAGE",
                 "DEF:tcpOutSegs=$database:tcpOutSegs:AVERAGE",
                 "DEF:tcpRetransSegs=$database:tcpRetransSegs:AVERAGE",
                 "COMMENT:Packets/sec    Current    Average   Maximum\\n",
                 "LINE1.25:tcpActiveOpens#00cc00:ActiveOpens ",
                 "GPRINT:tcpActiveOpens:LAST:%6.2lf%s",
                 "GPRINT:tcpActiveOpens:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpActiveOpens:MAX: %6.2lf%s\\n",
                 "LINE1.25:tcpPassiveOpens#006600:PassiveOpens",
                 "GPRINT:tcpPassiveOpens:LAST:%6.2lf%s",
                 "GPRINT:tcpPassiveOpens:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpPassiveOpens:MAX: %6.2lf%s\\n",
                 "LINE1.25:tcpAttemptFails#cc0000:AttemptFails",
                 "GPRINT:tcpAttemptFails:LAST:%6.2lf%s",
                 "GPRINT:tcpAttemptFails:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpAttemptFails:MAX: %6.2lf%s\\n",
                 "LINE1.25:tcpEstabResets#660000:EstabResets ",
                 "GPRINT:tcpEstabResets:LAST:%6.2lf%s",
                 "GPRINT:tcpEstabResets:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpEstabResets:MAX: %6.2lf%s\\n",
                 "LINE1.25:tcpInSegs#0066cc:InSegs      ",
                 "GPRINT:tcpInSegs:LAST:%6.2lf%s",
                 "GPRINT:tcpInSegs:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpInSegs:MAX: %6.2lf%s\\n",
                 "LINE1.25:tcpOutSegs#003399:OutSegs     ",
                 "GPRINT:tcpOutSegs:LAST:%6.2lf%s",
                 "GPRINT:tcpOutSegs:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpOutSegs:MAX: %6.2lf%s\\n",
                 "LINE1.25:tcpRetransSegs#cc00cc:RetransSegs ",
                 "GPRINT:tcpRetransSegs:LAST:%6.2lf%s",
                 "GPRINT:tcpRetransSegs:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpRetransSegs:MAX: %6.2lf%s\\n",
                 );
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font", "--font", "AXIS:6:$mono_font", "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error(); echo "rrd_graph() ERROR: $err\n"; return FALSE;
  } else {
    return $imgfile;
  }
}

function udp_graph ($rrd, $graph, $from, $to, $width, $height) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--alt-autoscale-max", "-E", "-l 0",
                 "DEF:udpInDatagrams=$database:udpInDatagrams:AVERAGE",
                 "DEF:udpOutDatagrams=$database:udpOutDatagrams:AVERAGE",
                 "DEF:udpInErrors=$database:udpInErrors:AVERAGE",
                 "DEF:udpNoPorts=$database:udpNoPorts:AVERAGE",
                 "COMMENT:Packets/sec    Current    Average   Maximum\\n",
                 "LINE1.25:udpInDatagrams#00cc00:InDatagrams ",
                 "GPRINT:udpInDatagrams:LAST:%6.2lf%s",
                 "GPRINT:udpInDatagrams:AVERAGE: %6.2lf%s",
                 "GPRINT:udpInDatagrams:MAX: %6.2lf%s\\n",
                 "LINE1.25:udpOutDatagrams#006600:OutDatagrams",
                 "GPRINT:udpOutDatagrams:LAST:%6.2lf%s",
                 "GPRINT:udpOutDatagrams:AVERAGE: %6.2lf%s",
                 "GPRINT:udpOutDatagrams:MAX: %6.2lf%s\\n",
                 "LINE1.25:udpInErrors#cc0000:InErrors    ",
                 "GPRINT:udpInErrors:LAST:%6.2lf%s",
                 "GPRINT:udpInErrors:AVERAGE: %6.2lf%s",
                 "GPRINT:udpInErrors:MAX: %6.2lf%s\\n",
                 "LINE1.25:udpNoPorts#660000:NoPorts     ",
                 "GPRINT:udpNoPorts:LAST:%6.2lf%s",
                 "GPRINT:udpNoPorts:AVERAGE: %6.2lf%s",
                 "GPRINT:udpNoPorts:MAX: %6.2lf%s\\n",
                 );
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font", "--font", "AXIS:6:$mono_font", "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error(); echo "rrd_graph() ERROR: $err\n"; return FALSE;
  } else {
    return $imgfile;
  }
}


?>
