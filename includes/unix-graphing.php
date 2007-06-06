<?php 

// Start Graphing Functions

function mailerrorgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $range = $to - $from;
  $points_per_sample = 3;
  $xpoints = '540';
  $step = $range*$points_per_sample/$xpoints;
  $rrd_virus = "rrd/" . $rrd . "-mail_virus.rrd";
  $rrd = "rrd/" . $rrd . "-mail.rrd";
  $imgfile = "graphs/" . "$graph";
  $optsa = array(
    "-E", 
    "--lower-limit", "0",
    "--units-exponent", "0",
    "--title", $title,
    "--vertical-label", $vertical,
    "-l 0",
    "--width", $width, "--height",  $height,
    "--start", $from, "--end", $to,
    "DEF:rejected=$rrd:rejected:AVERAGE",
    "DEF:mrejected=$rrd:rejected:MAX",
    "CDEF:rrejected=rejected,60,*",
    "CDEF:drejected=rejected,UN,0,rejected,IF,$step,*",
    "CDEF:srejected=PREV,UN,drejected,PREV,IF,drejected,+",
    "CDEF:rmrejected=mrejected,60,*",
    "DEF:bounced=$rrd:bounced:AVERAGE",
    "DEF:mbounced=$rrd:bounced:MAX",
    "CDEF:rbounced=bounced,60,*",
    "CDEF:dbounced=bounced,UN,0,bounced,IF,$step,*",
    "CDEF:sbounced=PREV,UN,dbounced,PREV,IF,dbounced,+",
    "CDEF:rmbounced=mbounced,60,*",
    "DEF:virus=$rrd_virus:virus:AVERAGE",
    "DEF:mvirus=$rrd_virus:virus:MAX",
    "CDEF:rvirus=virus,60,*",
    "CDEF:dvirus=virus,UN,0,virus,IF,$step,*",
    "CDEF:svirus=PREV,UN,dvirus,PREV,IF,dvirus,+",
    "CDEF:rmvirus=mvirus,60,*",
    "DEF:spam=$rrd_virus:spam:AVERAGE",
    "DEF:mspam=$rrd_virus:spam:MAX",
    "CDEF:rspam=spam,60,*",
    "CDEF:dspam=spam,UN,0,spam,IF,$step,*",
    "CDEF:sspam=PREV,UN,dspam,PREV,IF,dspam,+",
    "CDEF:rmspam=mspam,60,*",
    "LINE2:rrejected#cc0000:reject",
    'GPRINT:srejected:MAX:tot\: %6.0lf msgs',
    'GPRINT:rrejected:AVERAGE:avg\: %5.2lf/min',
    'GPRINT:rmrejected:MAX:max\: %3.0lf/min\l',
    "AREA:rbounced#0000cc:bounce",
    'GPRINT:sbounced:MAX:tot\: %6.0lf msgs',
    'GPRINT:rbounced:AVERAGE:avg\: %5.2lf/min',
    'GPRINT:rmbounced:MAX:max\: %3.0lf/min\l',
    "STACK:rvirus#000000:virus ",
    'GPRINT:svirus:MAX:tot\: %6.0lf msgs',
    'GPRINT:rvirus:AVERAGE:avg\: %5.2lf/min',
    'GPRINT:rmvirus:MAX:max\: %3.0lf/min\l',
    "STACK:rspam#00cc00:spam  ",
    'GPRINT:sspam:MAX:tot\: %6.0lf msgs',
    'GPRINT:rspam:AVERAGE:avg\: %5.2lf/min',
    'GPRINT:rmspam:MAX:max\: %3.0lf/min\l');

  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);


    $ret = rrd_graph("$imgfile", $opts, count($opts));
    if( !is_array($ret) ) {
       $err = rrd_error();
#       echo "rrd_graph() ERROR: $err\n";
       return FALSE;
    } else {
       return $imgfile;
    }
}



function mailsgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $points_per_sample = 3;
  $range = $to - $from;
  $xpoints = '540';
  $step = $range*$points_per_sample/$xpoints;
  $rrd = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array(
    "-E", 
    "--lower-limit", "0",
    "--units-exponent", "0",
    "--title", $title,
    "--vertical-label", $vertical,
    "-l 0",
    "--width", $width, "--height",  $height,
    "--start",
    $from, "--end", $to,
    "DEF:sent=$rrd:sent:AVERAGE",
    "DEF:msent=$rrd:sent:MAX",
    "CDEF:rsent=sent,60,*",
    "CDEF:rmsent=msent,60,*",
    "CDEF:dsent=sent,UN,0,sent,IF,$step,*",
    "CDEF:ssent=PREV,UN,dsent,PREV,IF,dsent,+",
    "DEF:recv=$rrd:recv:AVERAGE",
    "DEF:mrecv=$rrd:recv:MAX",
    "CDEF:rrecv=recv,60,*",
    "CDEF:rmrecv=mrecv,60,*",
    "CDEF:drecv=recv,UN,0,recv,IF,$step,*",
    "CDEF:srecv=PREV,UN,drecv,PREV,IF,drecv,+",
    "AREA:rsent#00c000:sent",
    "LINE1:rsent#005000:",
    "GPRINT:ssent:MAX:Tot\: %5.0lf msgs",
    "GPRINT:rsent:AVERAGE:Avg\: %4.2lf/min",
    "GPRINT:rmsent:MAX:Max\: %3.0lf/min\l",
    "LINE1.5:rrecv#cc0000:rcvd",
    "GPRINT:srecv:MAX:Tot\: %5.0lf msgs",
    "GPRINT:rrecv:AVERAGE:Avg\: %4.2lf/min",
    "GPRINT:rmrecv:MAX:Max\: %3.0lf/min\l");

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

function memgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical)
{
 global $rrdtool, $installdir, $mono_font;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
    $optsa = array ( 
    "--start", $from, "--end", $to,
    "-b 1024", 
    "-E", 
    "-v", $vertical,
    "--title", $title,
    "-l 0",
    "--width",  $width, "--height", $height,
    "DEF:atotalswap=$database:totalswap:AVERAGE",
    "DEF:aavailswap=$database:availswap:AVERAGE",
    "DEF:atotalreal=$database:totalreal:AVERAGE",
    "DEF:aavailreal=$database:availreal:AVERAGE",
    "DEF:atotalfree=$database:totalfree:AVERAGE",
    "DEF:ashared=$database:shared:AVERAGE",
    "DEF:abuffered=$database:buffered:AVERAGE",
    "DEF:acached=$database:cached:AVERAGE",
    "CDEF:totalswap=atotalswap,1024,*",
    "CDEF:availswap=aavailswap,1024,*",
    "CDEF:totalreal=atotalreal,1024,*",
    "CDEF:availreal=aavailreal,1024,*",
    "CDEF:totalfree=atotalfree,1024,*",
    "CDEF:shared=ashared,1024,*",
    "CDEF:buffered=abuffered,1024,*",
    "CDEF:cached=acached,1024,*",
    "CDEF:usedreal=totalreal,availreal,-",
    "CDEF:usedswap=totalswap,availswap,-",
    "CDEF:cusedswap=usedswap,-1,*",
    "CDEF:cdeftot=availreal,shared,buffered,usedreal,cached,usedswap,+,+,+,+,+",
    "COMMENT:Bytes       Current    Average     Maximum\\n",
    "LINE1:usedreal#d0b080:",
    "AREA:usedreal#f0e0a0:used",
    "GPRINT:usedreal:LAST:   %7.2lf %s",
    "GPRINT:usedreal:AVERAGE:%7.2lf %s",
    "GPRINT:usedreal:MAX:%7.2lf %s\\n",
    "STACK:availreal#e5e5e5:free",
    "GPRINT:availreal:LAST:   %7.2lf %s",
    "GPRINT:availreal:AVERAGE:%7.2lf %s",
    "GPRINT:availreal:MAX:%7.2lf %s\\n",
    "LINE1:usedreal#d0b080:",
    "AREA:shared#afeced::",
    "AREA:buffered#cc0000::STACK",
    "AREA:cached#ffaa66::STACK",
    "LINE1.25:shared#008fea:shared",
    "GPRINT:shared:LAST: %7.2lf %s",
    "GPRINT:shared:AVERAGE:%7.2lf %s",
    "GPRINT:shared:MAX:%7.2lf %s\\n",
    "LINE1.25:buffered#ff1a00:buffers:STACK",
    "GPRINT:buffered:LAST:%7.2lf %s",
    "GPRINT:buffered:AVERAGE:%7.2lf %s",
    "GPRINT:buffered:MAX:%7.2lf %s\\n",
    "LINE1.25:cached#ea8f00:cached:STACK",
    "GPRINT:cached:LAST: %7.2lf %s",
    "GPRINT:cached:AVERAGE:%7.2lf %s",
    "GPRINT:cached:MAX:%7.2lf %s\\n",
    "LINE1:totalreal#050505:",
    "AREA:cusedswap#C3D9FF:swap",
    "LINE1.25:cusedswap#356AA0:",
    "GPRINT:usedswap:LAST:   %7.2lf %s",
    "GPRINT:usedswap:AVERAGE:%7.2lf %s",
    "GPRINT:usedswap:MAX:%7.2lf %s\\n",
    "LINE1:totalreal#050505:total",
    "GPRINT:totalreal:AVERAGE:  %7.2lf %s");

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

function loadgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
    global $rrdtool, $installdir, $mono_font;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
    $optsa = array(
    "--title", $title,
    "--start",
    $from, "--end", $to,
    "-E", 
    "-v", $vertical,
    "--rigid",
    "--alt-autoscale-max",
    "-l 0",
    "--width", $width, "--height", $height,
    "DEF:1min=$database:1min:AVERAGE",
    "DEF:5min=$database:5min:AVERAGE",
    "DEF:15min=$database:15min:AVERAGE",
    "CDEF:a=1min,100,/",
    "CDEF:b=5min,100,/",
    "CDEF:c=15min,100,/",
    "CDEF:cdefd=a,b,c,+,+",
    "COMMENT:Load Average  Current    Average    Maximum\\n",
    "AREA:a#ffeeaa:1 Min:",
    "LINE1:a#c5aa00:",
    "GPRINT:a:LAST:    %7.2lf",
    "GPRINT:a:AVERAGE:  %7.2lf",
    "GPRINT:a:MAX:  %7.2lf\\n",
    "LINE1.25:b#ea8f00:5 Min:",
    "GPRINT:b:LAST:    %7.2lf",
    "GPRINT:b:AVERAGE:  %7.2lf",
    "GPRINT:b:MAX:  %7.2lf\\n",
    "LINE1.25:c#cc0000:15 Min",
    "GPRINT:c:LAST:   %7.2lf",
    "GPRINT:c:AVERAGE:  %7.2lf",
    "GPRINT:c:MAX:  %7.2lf\\n");

  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);


    $ret = rrd_graph("$imgfile", $opts, count($opts));
    if( !is_array($ret) ) {
       $err = rrd_error();
 #      echo "rrd_graph() ERROR: $err\n";
       return FALSE;
    } else {
       return $imgfile;
    }
}


function usersgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array(
    "--title", $title,
    "--start",
    $from, "--end", $to,
    "-E",
    "-v", $vertical,
    "--rigid",
    "--alt-autoscale-max",
    "-l 0",
    "--width", $width, "--height", $height,
    "DEF:users=$database:users:AVERAGE",
    "COMMENT:Users       Cur     Ave      Min     Max\\n",
    "AREA:users#CDEB8B:",
    "LINE1.25:users#008C00: ",
    "GPRINT:users:LAST:    %6.2lf",
    "GPRINT:users:AVERAGE:%6.2lf",
    "GPRINT:users:MIN:%6.2lf",
    "GPRINT:users:MAX:%6.2lf\\n");
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);

    $ret = rrd_graph("$imgfile", $opts, count($opts));
    if( !is_array($ret) ) {
       $err = rrd_error();
  #     echo "rrd_graph() ERROR: $err\n";
       return FALSE;
    } else {
       return $imgfile;
    }
}

function procsgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array(
    "--title", $title,
    "--start", $from, "--end", $to,
    "-E",
    "-v", $vertical,
    "--rigid",
    "--alt-autoscale-max",
    "-l 0",
    "--width", $width, "--height", $height,
    "DEF:procs=$database:procs:AVERAGE",
    "DEF:maxprocs=$database:procs:MAX",
    "COMMENT:Processes   Cur     Ave      Min     Max\\n",
    "AREA:procs#C3D9FF:",
    "LINE1.25:procs#356AA0: ",
    "GPRINT:procs:LAST:    %6.2lf",
    "GPRINT:procs:AVERAGE:%6.2lf",
    "GPRINT:procs:MIN:%6.2lf",
    "GPRINT:procs:MAX:%6.2lf\\n");
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



function cpugraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array(
    "--title", $title,
    "--start",
    $from, "--end", $to,
    "-E",
    "-v", $vertical,
    "--rigid",
    "--alt-autoscale-max",
    "-l 0",
    "--width", $width, "--height", $height,
    "DEF:user=$database:user:AVERAGE",
    "DEF:nice=$database:nice:AVERAGE",
    "DEF:system=$database:system:AVERAGE",
    "DEF:idle=$database:idle:AVERAGE",
    "CDEF:total=user,nice,system,idle,+,+,+",
    "CDEF:user_perc=user,total,/,100,*",
    "CDEF:nice_perc=nice,total,/,100,*",
    "CDEF:system_perc=system,total,/,100,*",
    "CDEF:idle_perc=idle,total,/,100,*",
    "AREA:user_perc#c02020:user",
    "GPRINT:user_perc:LAST:  Cur\:%3.0lf%%",
    "GPRINT:user_perc:AVERAGE: Avg\:%3.0lf%%",
    "GPRINT:user_perc:MAX: Max\:%3.0lf%%\\n",
    "AREA:nice_perc#008f00:nice:STACK",
    "GPRINT:nice_perc:LAST:  Cur\:%3.0lf%%",
    "GPRINT:nice_perc:AVERAGE: Avg\:%3.0lf%%",
    "GPRINT:nice_perc:MAX: Max\:%3.0lf%%\\n",
    "AREA:system_perc#ea8f00:system:STACK",
    "GPRINT:system_perc:LAST:Cur\:%3.0lf%%",
    "GPRINT:system_perc:AVERAGE: Avg\:%3.0lf%%",
    "GPRINT:system_perc:MAX: Max\:%3.0lf%%\\n",
    "AREA:idle_perc#f5f5e5:idle:STACK",
    "GPRINT:idle_perc:LAST:  Cur\:%3.0lf%%",
    "GPRINT:idle_perc:AVERAGE: Avg\:%3.0lf%%",
    "GPRINT:idle_perc:MAX: Max\:%3.0lf%%\\n");

  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);

  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
#    $err = rrd_error();
    return FALSE;
  } else {
    return $imgfile;
  }
}

function couriergraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $points_per_sample = 3;
  $range = $to - $from;
  $rrd = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array(
    "-E", 
    "--lower-limit", "0",
    "--units-exponent", "0",
    "--title", $title,
    "--vertical-label", $vertical,
    "-l 0",
    "--width", $width, "--height",  $height,
    "--start", $from, "--end", $to,
                "DEF:pop3d_login=$rrd:pop3d_login:AVERAGE",
                "DEF:mpop3d_login=$rrd:pop3d_login:MAX",
                "DEF:imapd_login=$rrd:imapd_login:AVERAGE",
                "DEF:mimapd_login=$rrd:imapd_login:MAX",
                "CDEF:rpop3d_login=pop3d_login,60,*",
		"CDEF:vpop3d_login=pop3d_login,UN,0,pop3d_login,IF,$range,*",
                "CDEF:rmpop3d_login=mpop3d_login,60,*",
                "CDEF:rimapd_login=imapd_login,60,*",
		"CDEF:vimapd_login=imapd_login,UN,0,imapd_login,IF,$range,*",
                "CDEF:rmimapd_login=mimapd_login,60,*",
                "DEF:pop3d_ssl_login=$rrd:pop3d_ssl_login:AVERAGE",
                "DEF:mpop3d_ssl_login=$rrd:pop3d_ssl_login:MAX",
                "DEF:imapd_ssl_login=$rrd:imapd_ssl_login:AVERAGE",
                "DEF:mimapd_ssl_login=$rrd:imapd_ssl_login:MAX",
                "CDEF:rpop3d_ssl_login=pop3d_ssl_login,60,*",
		"CDEF:vpop3d_ssl_login=pop3d_ssl_login,UN,0,pop3d_ssl_login,IF,$range,*",
                "CDEF:rmpop3d_ssl_login=mpop3d_ssl_login,60,*",
                "CDEF:rimapd_ssl_login=imapd_ssl_login,60,*",
                "CDEF:rmimapd_ssl_login=mimapd_ssl_login,60,*",
		"CDEF:vimapd_ssl_login=imapd_ssl_login,UN,0,imapd_ssl_login,IF,$range,*",
                'LINE1.5:rpop3d_login#BB0000:pop3',
                'GPRINT:vpop3d_login:AVERAGE:    tot\: %5.0lf',
                'GPRINT:rpop3d_login:AVERAGE:avg\: %4.0lf/min',
                'GPRINT:rmpop3d_login:MAX:max\: %4.0lf/min\l',
                'LINE1.5:rimapd_login#009900:imap',
                'GPRINT:vimapd_login:AVERAGE:    tot\: %5.0lf',
		'GPRINT:rimapd_login:AVERAGE:avg\: %4.0lf/min',
                'GPRINT:rmimapd_login:MAX:max\: %4.0lf/min\l',
                'LINE1.5:rpop3d_ssl_login#000000:pop3-ssl',
                'GPRINT:vpop3d_ssl_login:AVERAGE:tot\: %5.0lf',
  		'GPRINT:rpop3d_ssl_login:AVERAGE:avg\: %4.0lf/min',
                'GPRINT:rmpop3d_ssl_login:MAX:max\: %4.0lf/min\l',
                'LINE1.5:rimapd_ssl_login#000099:imap-ssl',
                'GPRINT:vimapd_ssl_login:AVERAGE:tot\: %5.0lf',
		'GPRINT:rimapd_ssl_login:AVERAGE:avg\: %4.0lf/min',
                'GPRINT:rmimapd_ssl_login:MAX:max\: %4.0lf/min\l');
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);


    $ret = rrd_graph("$imgfile", $opts, count($opts));
    if( !is_array($ret) ) {
       $err = rrd_error();
#       echo "rrd_graph() ERROR: $err\n";
       return FALSE;
    } else {
       return $imgfile;
    }
}

function apachehitsgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--vertical-label", $vertical ,"--alt-autoscale-max",
                 "-l 0",
                 "-E",
                 "--title", $title,
                 "DEF:hits=$database:hits:AVERAGE",
                 "COMMENT:            Current     Average     Maximum\\n",
                 "AREA:hits#ff9933:",
                 "LINE1.25:hits#FF6600:Hits",
                 "GPRINT:hits:LAST: %6.2lf/sec",
                 "GPRINT:hits:AVERAGE:%6.2lf/sec",
                 "GPRINT:hits:MAX:%6.2lf/sec\\n");
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

function unixfsgraph ($id, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--vertical-label", $vertical, "--alt-autoscale-max",
                 "-l 0",
                 "-E",
                 "-b 1024",
                 "--title", $title);
  $imgfile = "graphs/" . "$graph";
  $iter = "1";
  $sql = mysql_query("SELECT * FROM storage where storage_id = '$id'");
  $optsa[] = "COMMENT:                       Size      Used    %age\l";
  while($fs = mysql_fetch_array($sql)) {
    $hostname = gethostbyid($fs['host_id']);
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; $iter = "0"; }

    $descr = str_pad($fs[hrStorageDescr], 16);
    $descr = substr($descr,0,16);


    $text = str_replace("/", "_", $fs['hrStorageDescr']);
    $optsa[] = "DEF:$fs[storage_id]=rrd/$hostname-storage-$text.rrd:used:AVERAGE";
    $optsa[] = "DEF:$fs[storage_id]s=rrd/$hostname-storage-$text.rrd:size:AVERAGE";
    $optsa[] = "DEF:$fs[storage_id]p=rrd/$hostname-storage-$text.rrd:perc:AVERAGE";
    $optsa[] = "LINE1.25:$fs[storage_id]p#" . $colour . ":$descr";
    $optsa[] = "GPRINT:$fs[storage_id]s:LAST:%6.2lf%SB";
    $optsa[] = "GPRINT:$fs[storage_id]:LAST:%6.2lf%SB";
    $optsa[] = "GPRINT:$fs[storage_id]p:LAST:%3.0lf%%\l";
    $iter++;
  }
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error();
    #echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}


function unixfsgraph_dev ($device, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--vertical-label", $vertical, "--alt-autoscale-max",
                 "-l 0",
                 "-E", 
	         "-b 1024",
                 "--title", $title);
  $hostname = gethostbyid($device);
  $imgfile = "graphs/" . "$graph";
  $iter = "1";
  $sql = mysql_query("SELECT * FROM storage where host_id = '$device'");
  $optsa[] = "COMMENT:                       Size      Used    %age\l";
  while($fs = mysql_fetch_array($sql)) {
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; $iter = "0"; }

    $descr = str_pad($fs[hrStorageDescr], 16);
    $descr = substr($descr,0,16);


    $text = str_replace("/", "_", $fs['hrStorageDescr']);
    $optsa[] = "DEF:$fs[storage_id]=rrd/$hostname-storage-$text.rrd:used:AVERAGE";
    $optsa[] = "DEF:$fs[storage_id]s=rrd/$hostname-storage-$text.rrd:size:AVERAGE";
    $optsa[] = "DEF:$fs[storage_id]p=rrd/$hostname-storage-$text.rrd:perc:AVERAGE";
    $optsa[] = "LINE1.25:$fs[storage_id]p#" . $colour . ":$descr";
    $optsa[] = "GPRINT:$fs[storage_id]s:LAST:%6.2lf%SB";
    $optsa[] = "GPRINT:$fs[storage_id]:LAST:%6.2lf%SB";
    $optsa[] = "GPRINT:$fs[storage_id]p:LAST:%3.0lf%%\l";
    $iter++;
  }
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error();
    #echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}

function apachebitsgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--vertical-label", $vertical,"--alt-autoscale-max",
                 "-l 0",
                 "-E", 
                 "--title", $title,
                 "DEF:bits=$database:bits:AVERAGE",
                 "COMMENT:        Current      Average     Maximum\\n",
                 "AREA:bits#cccc00:",
                 "LINE1.25:bits#999900:Bits",
                 "GPRINT:bits:LAST:%6.2lf%sbps",
                 "GPRINT:bits:AVERAGE:%6.2lf%sbps",
                 "GPRINT:bits:MAX:%6.2lf%sbps\\n");

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
