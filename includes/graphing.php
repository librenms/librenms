<?php

include("graphing/screenos.php");
include("graphing/fortigate.php");
include("graphing/windows.php");
include("graphing/unix.php");

function graph_multi_bits_trio ($ports, $graph, $from, $to, $width, $height, $title, $vertical, $inverse, $legend = '1') {
  global $config, $installdir;
  $options = " --alt-autoscale-max -E --start $from --end " . ($to - 150) . " --width $width --height $height ";
  $options .= $config['rrdgraph_def_text'];
  if($height < "99") { $options .= " --only-graph"; }
  $i = 1;
  foreach(explode(",", $ports[0]) as $ifid) {
    $query = mysql_query("SELECT `ifIndex`, `hostname` FROM `ports` AS I, devices as D WHERE I.interface_id = '" . $ifid . "' AND I.device_id = D.device_id");
    $int = mysql_fetch_row($query);
    if(is_file($config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd")) {
      if(strstr($inverse, "a")) { $in = "OUT"; $out = "IN"; } else { $in = "IN"; $out = "OUT"; }
      $options .= " DEF:inoctets" . $i . "=" . $config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd:".$in."OCTETS:AVERAGE";
      $options .= " DEF:outoctets" . $i . "=" . $config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd:".$out."OCTETS:AVERAGE";
      $in_thing .= $seperator . "inoctets" . $i . ",UN,0," . "inoctets" . $i . ",IF";
      $out_thing .= $seperator . "outoctets" . $i . ",UN,0," . "outoctets" . $i . ",IF";
      $pluses .= $plus;
      $seperator = ",";
      $plus = ",+";
      $i++;
    }
  }
  unset($seperator); unset($plus);
  foreach(explode(",", $ports[1]) as $ifid) {
    $query = mysql_query("SELECT `ifIndex`, `hostname` FROM `ports` AS I, devices as D WHERE I.interface_id = '" . $ifid . "' AND I.device_id = D.device_id");
    $int = mysql_fetch_row($query);
    if(is_file($config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd")) {
      if(strstr($inverse, "b")) { $in = "OUT"; $out = "IN"; } else { $in = "IN"; $out = "OUT"; }
      $options .= " DEF:inoctetsb" . $i . "=" . $config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd:".$in."OCTETS:AVERAGE";
      $options .= " DEF:outoctetsb" . $i . "=" . $config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd:".$out."OCTETS:AVERAGE";
      $in_thingb .= $seperator . "inoctetsb" . $i . ",UN,0," . "inoctetsb" . $i . ",IF";
      $out_thingb .= $seperator . "outoctetsb" . $i . ",UN,0," . "outoctetsb" . $i . ",IF";
      $plusesb .= $plus;
      $seperator = ",";
      $plus = ",+";
      $i++;
    }
  }
  unset($seperator); unset($plus);
  foreach(explode(",", $ports[2]) as $ifid) {
    $query = mysql_query("SELECT `ifIndex`, `hostname` FROM `ports` AS I, devices as D WHERE I.interface_id = '" . $ifid . "' AND I.device_id = D.device_id");
    $int = mysql_fetch_row($query);
    if(is_file($config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd")) {
      if(strstr($inverse, "c")) { $in = "OUT"; $out = "IN"; } else { $in = "IN"; $out = "OUT"; }
      $options .= " DEF:inoctetsc" . $i . "=" . $config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd:".$in."OCTETS:AVERAGE";
      $options .= " DEF:outoctetsc" . $i . "=" . $config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd:".$out."OCTETS:AVERAGE";
      $in_thingc .= $seperator . "inoctetsc" . $i . ",UN,0," . "inoctetsc" . $i . ",IF";
      $out_thingc .= $seperator . "outoctetsc" . $i . ",UN,0," . "outoctetsc" . $i . ",IF";
      $plusesc .= $plus;
      $seperator = ",";
      $plus = ",+";
      $i++;
    }
  }
  $options .= " CDEF:inoctets=" . $in_thing . $pluses;
  $options .= " CDEF:outoctets=" . $out_thing . $pluses;
  $options .= " CDEF:inoctetsb=" . $in_thingb . $plusesb;
  $options .= " CDEF:outoctetsb=" . $out_thingb . $plusesb;
  $options .= " CDEF:inoctetsc=" . $in_thingc . $plusesc;
  $options .= " CDEF:outoctetsc=" . $out_thingc . $plusesc;
  $options .= " CDEF:doutoctets=outoctets,-1,*";
  $options .= " CDEF:inbits=inoctets,8,*";
  $options .= " CDEF:outbits=outoctets,8,*";
  $options .= " CDEF:doutbits=doutoctets,8,*";
  $options .= " CDEF:doutoctetsb=outoctetsb,-1,*";
  $options .= " CDEF:inbitsb=inoctetsb,8,*";
  $options .= " CDEF:outbitsb=outoctetsb,8,*";
  $options .= " CDEF:doutbitsb=doutoctetsb,8,*";
  $options .= " CDEF:doutoctetsc=outoctetsc,-1,*";
  $options .= " CDEF:inbitsc=inoctetsc,8,*";
  $options .= " CDEF:outbitsc=outoctetsc,8,*";
  $options .= " CDEF:doutbitsc=doutoctetsc,8,*";
  $options .= " CDEF:inbits_tot=inbits,inbitsb,inbitsc,+,+";
  $options .= " CDEF:outbits_tot=outbits,outbitsb,outbitsc,+,+";
  $options .= " CDEF:inbits_stot=inbitsc,inbitsb,+";
  $options .= " CDEF:outbits_stot=outbitsc,outbitsb,+";
  $options .= " CDEF:doutbits_stot=outbits_stot,-1,*";
  $options .= " CDEF:doutbits_tot=outbits_tot,-1,*";
  $options .= " CDEF:nothing=outbits_tot,outbits_tot,-";

  if($legend == "no") {
   $options .= " AREA:inbits_tot#cdeb8b:";
   $options .= " AREA:doutbits_tot#cdeb8b:";
   $options .= " LINE1.25:inbits_tot#aacc77:";
   $options .= " LINE1.25:doutbits_tot#aacc88:";
   $options .= " AREA:inbits_stot#c3d9ff:";
   $options .= " AREA:doutbits_stot#c3d9ff:";
   $options .= " LINE1:inbits_stot#b3a9cf:";
   $options .= " LINE1:doutbits_stot#b3a9cf:";
   $options .= " AREA:inbitsc#ffcc99:";
   $options .= " AREA:doutbitsc#ffcc99:";
   $options .= " LINE1.25:inbitsc#ddaa88";
   $options .= " LINE1.25:doutbitsc#ddaa88";
   $options .= " LINE1:inbits#006600:";
   $options .= " LINE1:doutbits#006600:";
   $options .= " LINE1:inbitsb#000099:";
   $options .= " LINE1:doutbitsb#000099:";
   $options .= " LINE0.5:nothing#555555:";
  } else {
   $options .= " COMMENT:BPS\ \ \ \ \ \ \ \ \ \ \ \ Current\ \ \ Average\ \ \ \ \ \ Min\ \ \ \ \ \ Max\\\\n";
   $options .= " AREA:inbits_tot#cdeb8b:ATM\ \ In\ ";
   $options .= " GPRINT:inbits:LAST:%6.2lf%s";
   $options .= " GPRINT:inbits:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:inbits:MIN:%6.2lf%s";
   $options .= " GPRINT:inbits:MAX:%6.2lf%s\\\\l";
   $options .= " AREA:doutbits_tot#cdeb8b:";
   $options .= " COMMENT:\ \ \ \ \ \ \ Out";
   $options .= " GPRINT:outbits:LAST:%6.2lf%s";
   $options .= " GPRINT:outbits:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:outbits:MIN:%6.2lf%s";
   $options .= " GPRINT:outbits:MAX:%6.2lf%s\\\\l";
   $options .= " LINE1.25:inbits_tot#aacc77:";
   $options .= " LINE1.25:doutbits_tot#aacc88:";
   $options .= " AREA:inbits_stot#c3d9ff:NGN\ \ In\ ";
   $options .= " GPRINT:inbitsb:LAST:%6.2lf%s";
   $options .= " GPRINT:inbitsb:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:inbitsb:MIN:%6.2lf%s";
   $options .= " GPRINT:inbitsb:MAX:%6.2lf%s\\\\l";
   $options .= " AREA:doutbits_stot#c3d9ff:";
   $options .= " COMMENT:\ \ \ \ \ \ \ Out";
   $options .= " GPRINT:outbitsb:LAST:%6.2lf%s";
   $options .= " GPRINT:outbitsb:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:outbitsb:MIN:%6.2lf%s";
   $options .= " GPRINT:outbitsb:MAX:%6.2lf%s\\\\l";
   $options .= " LINE1:inbits_stot#b3a9cf:";
   $options .= " LINE1:doutbits_stot#b3a9cf:";
   $options .= " AREA:inbitsc#ffcc99:Wave\ In\ ";
   $options .= " GPRINT:inbitsc:LAST:%6.2lf%s";
   $options .= " GPRINT:inbitsc:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:inbitsc:MIN:%6.2lf%s";
   $options .= " GPRINT:inbitsc:MAX:%6.2lf%s\\\\l";
   $options .= " AREA:doutbitsc#ffcc99:";
   $options .= " COMMENT:\ \ \ \ \ \ \ Out";
   $options .= " GPRINT:outbitsc:LAST:%6.2lf%s";
   $options .= " GPRINT:outbitsc:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:outbitsc:MIN:%6.2lf%s";
   $options .= " GPRINT:outbitsc:MAX:%6.2lf%s\\\\l";
   $options .= " LINE1.25:inbitsc#ddaa88";
   $options .= " LINE1.25:doutbitsc#ddaa88";
   $options .= " LINE1:inbits#006600:";
   $options .= " LINE1:doutbits#006600:";
   $options .= " LINE1:inbitsb#000099:";
   $options .= " LINE1:doutbitsb#000099:";
   $options .= " LINE0.5:nothing#555555:";

   $options .= " COMMENT:Total\ \ In\ ";
   $options .= " GPRINT:inbits_tot:LAST:%6.2lf%s";
   $options .= " GPRINT:inbits_tot:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:inbits_tot:MIN:%6.2lf%s";
   $options .= " GPRINT:inbits_tot:MAX:%6.2lf%s\\\\l";
   $options .= " COMMENT:\ \ \ \ \ \ \ Out";
   $options .= " GPRINT:outbits_tot:LAST:%6.2lf%s";
   $options .= " GPRINT:outbits_tot:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:outbits_tot:MIN:%6.2lf%s";
   $options .= " GPRINT:outbits_tot:MAX:%6.2lf%s\\\\l";


  }
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal"; }
  echo($config['rrdtool'] . " graph $graph $options");
  $thing = shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}


function graph_multi_bits_duo ($ports, $graph, $from, $to, $width, $height, $title, $vertical, $inverse, $legend = '1') {
  global $config, $installdir;
  $options = "--alt-autoscale-max -E --start $from --end " . ($to - 150) . " --width $width --height $height";
  $options .= $config['rrdgraph_def_text'];
  if($height < "99") { $options .= " --only-graph"; }
  $i = 1;
  foreach(explode(",", $ports[0]) as $ifid) {
    $query = mysql_query("SELECT `ifIndex`, `hostname` FROM `ports` AS I, devices as D WHERE I.interface_id = '" . $ifid . "' AND I.device_id = D.device_id");
    $int = mysql_fetch_row($query);
    if(is_file($config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd")) {
      $options .= " DEF:inoctets" . $i . "=" . $config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd:INOCTETS:AVERAGE";
      $options .= " DEF:outoctets" . $i . "=" . $config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd:OUTOCTETS:AVERAGE";
      $in_thing .= $seperator . "inoctets" . $i . ",UN,0," . "inoctets" . $i . ",IF";
      $out_thing .= $seperator . "outoctets" . $i . ",UN,0," . "outoctets" . $i . ",IF";
      $pluses .= $plus;
      $seperator = ",";
      $plus = ",+";
      $i++;
    }
  }
  unset($seperator); unset($plus);
  foreach(explode(",", $ports[1]) as $ifid) {
    $query = mysql_query("SELECT `ifIndex`, `hostname` FROM `ports` AS I, devices as D WHERE I.interface_id = '" . $ifid . "' AND I.device_id = D.device_id");
    $int = mysql_fetch_row($query);
    if(is_file($config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd")) {
      $options .= " DEF:inoctetsb" . $i . "=" . $config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd:INOCTETS:AVERAGE";
      $options .= " DEF:outoctetsb" . $i . "=" . $config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd:OUTOCTETS:AVERAGE";
      $in_thingb .= $seperator . "inoctetsb" . $i . ",UN,0," . "inoctetsb" . $i . ",IF";
      $out_thingb .= $seperator . "outoctetsb" . $i . ",UN,0," . "outoctetsb" . $i . ",IF";
      $plusesb .= $plus;
      $seperator = ",";
      $plus = ",+";
      $i++;
    }
  }
  if($inverse) { $in = 'out'; $out = 'in'; } else { $in = 'in'; $out = 'out'; }
  $options .= " CDEF:".$in."octets=" . $in_thing . $pluses;
  $options .= " CDEF:".$out."octets=" . $out_thing . $pluses;
  $options .= " CDEF:".$in."octetsb=" . $in_thingb . $plusesb;
  $options .= " CDEF:".$out."octetsb=" . $out_thingb . $plusesb;
  $options .= " CDEF:doutoctets=outoctets,-1,*";
  $options .= " CDEF:inbits=inoctets,8,*";
  $options .= " CDEF:outbits=outoctets,8,*";
  $options .= " CDEF:doutbits=doutoctets,8,*";
  $options .= " CDEF:doutoctetsb=outoctetsb,-1,*";
  $options .= " CDEF:inbitsb=inoctetsb,8,*";
  $options .= " CDEF:outbitsb=outoctetsb,8,*";
  $options .= " CDEF:doutbitsb=doutoctetsb,8,*";
  $options .= " CDEF:inbits_tot=inbits,inbitsb,+";
  $options .= " CDEF:outbits_tot=outbits,outbitsb,+";
  $options .= " CDEF:doutbits_tot=outbits_tot,-1,*";
  $options .= " CDEF:nothing=outbits_tot,outbits_tot,-";
  if($legend == "no") {
   $options .= " AREA:inbits_tot#cdeb8b:";
   $options .= " AREA:inbits#ffcc99:";
   $options .= " AREA:doutbits_tot#cdeb8b:";
   $options .= " AREA:doutbits#ffcc99:";
   $options .= " LINE1:inbits#aa9966:";
   $options .= " LINE1:doutbits#aa9966:";
   $options .= " LINE1:inbitsb#006600:";
   $options .= " LINE1:doutbitsb#006600:";
   $options .= " LINE1.25:inbits_tot#006600:";
   $options .= " LINE1.25:doutbits_tot#006600:";
   $options .= " LINE0.5:nothing#555555:";
  } else {
   $options .= " COMMENT:BPS\ \ \ \ \ \ \ \ \ \ \ \ Current\ \ \ Average\ \ \ \ \ \ Min\ \ \ \ \ \ Max\\\\n";
   $options .= " AREA:inbits_tot#cdeb8b:Peering\ In\ ";
   $options .= " GPRINT:inbitsb:LAST:%6.2lf%s";
   $options .= " GPRINT:inbitsb:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:inbitsb:MIN:%6.2lf%s";
   $options .= " GPRINT:inbitsb:MAX:%6.2lf%s\\\\l";
   $options .= " AREA:doutbits_tot#cdeb8b:";
   $options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ Out";
   $options .= " GPRINT:outbitsb:LAST:%6.2lf%s";
   $options .= " GPRINT:outbitsb:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:outbitsb:MIN:%6.2lf%s";
   $options .= " GPRINT:outbitsb:MAX:%6.2lf%s\\\\l";

   $options .= " AREA:inbits#ffcc99:Transit\ In\ ";
   $options .= " GPRINT:inbits:LAST:%6.2lf%s";
   $options .= " GPRINT:inbits:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:inbits:MIN:%6.2lf%s";
   $options .= " GPRINT:inbits:MAX:%6.2lf%s\\\\l";
   $options .= " AREA:doutbits#ffcc99:";
   $options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ Out";
   $options .= " GPRINT:outbits:LAST:%6.2lf%s";
   $options .= " GPRINT:outbits:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:outbits:MIN:%6.2lf%s";
   $options .= " GPRINT:outbits:MAX:%6.2lf%s\\\\l";

   $options .= " COMMENT:Total\ \ \ \ \ In\ ";
   $options .= " GPRINT:inbits_tot:LAST:%6.2lf%s";
   $options .= " GPRINT:inbits_tot:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:inbits_tot:MIN:%6.2lf%s";
   $options .= " GPRINT:inbits_tot:MAX:%6.2lf%s\\\\l";
   $options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ Out";
   $options .= " GPRINT:outbits_tot:LAST:%6.2lf%s";
   $options .= " GPRINT:outbits_tot:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:outbits_tot:MIN:%6.2lf%s";
   $options .= " GPRINT:outbits_tot:MAX:%6.2lf%s\\\\l";

   $options .= " LINE1:inbits#aa9966:";
   $options .= " LINE1:doutbits#aa9966:";
   $options .= " LINE1.25:inbitsb#006600:";
   $options .= " LINE1.25:doutbitsb#006600:";
   $options .= " LINE1.25:inbits_tot#006600:";
   $options .= " LINE1.25:doutbits_tot#006600:";
   $options .= " LINE0.5:nothing#555555:";

  }
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal"; }
  $thing = shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function graph_cbgp_prefixes ($rrd, $graph, $from, $to, $width, $height) {
  global $config;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  $options .= $config['rrdgraph_def_text'];
  if($width <= "300") {$options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:Accepted=$database:AcceptedPrefixes:AVERAGE";
  #$options .= " DEF:Denied=$database:DeniedPrefixes:AVERAGE";
  #$options .= " DEF:Advertised=$database:AdvertisedPrefixes:AVERAGE";
  #$options .= " DEF:Suppressed=$database:SuppressedPrefixes:AVERAGE";
  #$options .= " DEF:Withdrawn=$database:WithdrawnPrefixes:AVERAGE";
  #$options .= " CDEF:dAdvertised=Advertised,-1,*";
  $options .= " COMMENT:Prefixes\ \ \ \ \ \ Current\ \ Minimum\ \ Maximum\\\\n";
  $options .= " AREA:Accepted#eeaaaa:";
  $options .= " LINE2:Accepted#cc0000:Accepted\ \ ";
  $options .= " GPRINT:Accepted:LAST:%6.2lf%s";
  $options .= " GPRINT:Accepted:MIN:%6.2lf%s";
  $options .= " GPRINT:Accepted:MAX:%6.2lf%s\\\\l";
  #$options .= " AREA:dAdvertised#aaeeaa:";
  #$options .= " LINE2:dAdvertised#00cc00:Advertised";
  #$options .= " GPRINT:Advertised:LAST:%6.2lf%s";
  #$options .= " GPRINT:Advertised:MIN:%6.2lf%s";
  #$options .= " GPRINT:Advertised:MAX:%6.2lf%s\\\\l";
  $thing = shell_exec($config['rrdtool'] . " graph $graph $options");
#  echo($config['rrdtool'] . " graph $graph $options");
  return $graph;
}


function bgpupdatesgraph ($rrd, $graph , $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  $options .= $config['rrdgraph_def_text'];
  if($width <= "300") {$options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:in=$database:bgpPeerInUpdates:AVERAGE";
  $options .= " DEF:out=$database:bgpPeerOutUpdates:AVERAGE";
  $options .= " CDEF:dout=out,-1,*";
  $options .= " AREA:in#aa66aa:";
  $options .= " COMMENT:Updates\ \ \ \ Current\ \ \ \ \ Average\ \ \ \ \ \ Maximum\\\\n";
  $options .= " LINE1.25:in#330033:In\ \ ";
  $options .= " GPRINT:in:LAST:%6.2lf%sU/s";
  $options .= " GPRINT:in:AVERAGE:%6.2lf%sU/s";
  $options .= " GPRINT:in:MAX:%6.2lf%sU/s\\\\n";
  $options .= " AREA:dout#FFDD88:";
  $options .= " LINE1.25:dout#FF6600:Out\ ";
  $options .= " GPRINT:out:LAST:%6.2lf%sU/s";
  $options .= " GPRINT:out:AVERAGE:%6.2lf%sU/s";
  $options .= " GPRINT:out:MAX:%6.2lf%sU/s\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function graph_cpu_generic_single ($rrd, $graph , $from, $to, $width, $height) {
  global $config;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $options = "--alt-autoscale-max -l 0 -E --start $from --end $to --width $width --height $height ";
  $options .= $config['rrdgraph_def_text'];
  if($width <= "300") {$options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:cpu=$database:cpu:AVERAGE";
  $options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ Current\ \ Minimum\ \ Maximum\ \ Average\\\\n";
  $options .= " AREA:cpu#ffee99: LINE1.25:cpu#aa2200:Load\ %";
  $options .= " GPRINT:cpu:LAST:%6.2lf\  GPRINT:cpu:AVERAGE:%6.2lf\ ";
  $options .= " GPRINT:cpu:MAX:%6.2lf\  GPRINT:cpu:AVERAGE:%6.2lf\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}


function graph_adsl_rate ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $options = "--alt-autoscale-max -l 0 -E --start $from --end $to --width $width --height $height ";
  $options .= $config['rrdgraph_def_text'];
  if($width <= "300") {$options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:adslAtucCurrAtt=$database:adslAtucCurrAtt:AVERAGE";
  $options .= " DEF:adslAturCurrAtt=$database:adslAturCurrAtt:AVERAGE";
  $options .= " CDEF:dslAtucCurrAtt=adslAtucCurrAtt,1000,/";
  $options .= " CDEF:dslAturCurrAtt=adslAturCurrAtt,1000,/";
  $options .= " COMMENT:Bytes\ \ \ \ \ Current\ \ Minimum\ \ Maximum\ \ Average\\\\n";
  $options .= " LINE1.25:adslAtucCurrAtt#aa2200:Up\ \ \ \ ";
  $options .= " GPRINT:dslAtucCurrAtt:LAST:%5.0lfk\  GPRINT:dslAtucCurrAtt:AVERAGE:%5.0lfk\ ";
  $options .= " GPRINT:dslAtucCurrAtt:MAX:%5.0lfk\  GPRINT:dslAtucCurrAtt:AVERAGE:%5.0lfk\\\\n";
  $options .= " LINE1.25:adslAturCurrAtt#22aa00:Down\ \ ";
  $options .= " GPRINT:dslAturCurrAtt:LAST:%5.0lfk\  GPRINT:dslAturCurrAtt:AVERAGE:%5.0lfk\ ";
  $options .= " GPRINT:dslAturCurrAtt:MAX:%5.0lfk\  GPRINT:dslAturCurrAtt:AVERAGE:%5.0lfk\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function graph_adsl_snr ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $options = "--alt-autoscale-max -l 0 -E --start $from --end $to --width $width --height $height ";
  $options .= $config['rrdgraph_def_text'];
  if($width <= "300") {$options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:adslAtucCurrSnr=$database:adslAtucCurrSnr:AVERAGE";
  $options .= " DEF:adslAturCurrSnr=$database:adslAturCurrSnr:AVERAGE";
  $options .= " CDEF:dslAtucCurrSnr=adslAtucCurrSnr,10,/";
  $options .= " CDEF:dslAturCurrSnr=adslAturCurrSnr,10,/";
  $options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ Current\ \ Minimum\ \ Maximum\ \ Average\\\\n";
  $options .= " LINE1.25:dslAtucCurrSnr#aa2200:SNR\ Up\ \ ";
  $options .= " GPRINT:dslAtucCurrSnr:LAST:%3.1lfdB GPRINT:dslAtucCurrSnr:AVERAGE:%3.1lfdB\ ";
  $options .= " GPRINT:dslAtucCurrSnr:MAX:%3.1lfdB GPRINT:dslAtucCurrSnr:AVERAGE:%3.1lfdB\\\\n";
  $options .= " LINE1.25:dslAturCurrSnr#22aa00:SNR\ Down";
  $options .= " GPRINT:dslAturCurrSnr:LAST:%3.1lfdB GPRINT:dslAturCurrSnr:AVERAGE:%3.1lfdB\ ";
  $options .= " GPRINT:dslAturCurrSnr:MAX:%3.1lfdB GPRINT:dslAturCurrSnr:AVERAGE:%3.1lfdB\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function graph_adsl_atn ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $options = "--alt-autoscale-max -l 0 -E --start $from --end $to --width $width --height $height ";
  $options .= $config['rrdgraph_def_text'];
  if($width <= "300") {$options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:adslAtucCurrAtn=$database:adslAtucCurrAtn:AVERAGE";
  $options .= " DEF:adslAturCurrAtn=$database:adslAturCurrAtn:AVERAGE";
  $options .= " CDEF:dslAtucCurrAtn=adslAtucCurrAtn,10,/";
  $options .= " CDEF:dslAturCurrAtn=adslAturCurrAtn,10,/";
  $options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ Current\ \ Minimum\ \ Maximum\ \ Average\\\\n";
  $options .= " LINE1.25:dslAtucCurrAtn#aa2200:Atten\ Up\ \ ";
  $options .= " GPRINT:dslAtucCurrAtn:LAST:%3.1lfdB GPRINT:dslAtucCurrAtn:AVERAGE:%3.1lfdb";
  $options .= " GPRINT:dslAtucCurrAtn:MAX:%3.1lfdB GPRINT:dslAtucCurrAtn:AVERAGE:%3.1lfdb\\\\n";
  $options .= " LINE1.25:dslAturCurrAtn#22aa00:Atten\ Down";
  $options .= " GPRINT:dslAturCurrAtn:LAST:%3.1lfdB GPRINT:dslAturCurrAtn:AVERAGE:%3.1lfdb";
  $options .= " GPRINT:dslAturCurrAtn:MAX:%3.1lfdB GPRINT:dslAturCurrAtn:AVERAGE:%3.1lfdb\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function cpugraph ($rrd, $graph , $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $options = "--alt-autoscale-max -l 0 -E --start $from --end $to --width $width --height $height ";
  $options .= $config['rrdgraph_def_text'];
  if($width <= "300") {$options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:5s=$database:LOAD5S:AVERAGE";
  $options .= " DEF:5m=$database:LOAD5M:AVERAGE";
  $options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ Current\ \ Minimum\ \ Maximum\ \ Average\\\\n";
  $options .= " AREA:5m#ffee99: LINE1.25:5m#aa2200:Load\ %";
  $options .= " GPRINT:5m:LAST:%6.2lf\  GPRINT:5m:AVERAGE:%6.2lf\ ";
  $options .= " GPRINT:5m:MAX:%6.2lf\  GPRINT:5m:AVERAGE:%6.2lf\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function memgraph ($rrd, $graph , $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $period = $to - $from;
  $options = "-l 0 --alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  $options .= $config['rrdgraph_def_text'];
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:MEMTOTAL=$database:MEMTOTAL:AVERAGE";
  $options .= " DEF:IOFREE=$database:IOFREE:AVERAGE";
  $options .= " DEF:IOUSED=$database:IOUSED:AVERAGE";
  $options .= " DEF:PROCFREE=$database:PROCFREE:AVERAGE";
  $options .= " DEF:PROCUSED=$database:PROCUSED:AVERAGE";
  $options .= " CDEF:FREE=IOFREE,PROCFREE,+";
  $options .= " CDEF:USED=IOUSED,PROCUSED,+";
  $options .= " COMMENT:Bytes\ \ \ \ Current\ \ Minimum\ \ Maximum\ \ Average\\\\n";
  $options .= " AREA:USED#ff6060:";
  $options .= " LINE2:USED#cc0000:Used";
  $options .= " GPRINT:USED:LAST:%6.2lf%s";
  $options .= " GPRINT:USED:MIN:%6.2lf%s";
  $options .= " GPRINT:USED:MAX:%6.2lf%s";
  $options .= " GPRINT:USED:AVERAGE:%6.2lf%s\\\\l";
  $options .= " AREA:FREE#e5e5e5:Free:STACK";
  $options .= " GPRINT:FREE:LAST:%6.2lf%s";
  $options .= " GPRINT:FREE:MIN:%6.2lf%s";
  $options .= " GPRINT:FREE:MAX:%6.2lf%s";
  $options .= " GPRINT:FREE:AVERAGE:%6.2lf%s\\\\l";
  $options .= " LINE1:MEMTOTAL#000000:";
  $thing = shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

?>
