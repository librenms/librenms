<?php

include("graphing/screenos.php");
include("graphing/fortigate.php");
include("graphing/windows.php");
include("graphing/unix.php");

function graph_multi_bits ($interfaces, $graph, $from, $to, $width, $height, $title, $vertical, $inverse, $legend = '1') {
  global $config, $installdir;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -E --start $from --end " . ($to - 150) . " --width $width --height $height";
  if($height < "33") { $options .= " --only-graph"; }
  $i = 1;
  foreach(explode(",", $interfaces) as $ifid) {
    $query = mysql_query("SELECT `ifIndex`, `hostname` FROM `interfaces` AS I, devices as D WHERE I.interface_id = '" . $ifid . "' AND I.device_id = D.device_id");
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
  if($inverse) { $in = 'out'; $out = 'in'; } else { $in = 'in'; $out = 'out'; }
  $options .= " CDEF:".$in."octets=" . $in_thing . $pluses;
  $options .= " CDEF:".$out."octets=" . $out_thing . $pluses;
  $options .= " CDEF:doutoctets=outoctets,-1,*";
  $options .= " CDEF:inbits=inoctets,8,*";
  $options .= " CDEF:outbits=outoctets,8,*";
  $options .= " CDEF:doutbits=doutoctets,8,*";
  if($legend == "no") {
   $options .= " AREA:inbits#CDEB8B:";
   $options .= " LINE1.25:inbits#006600:";
   $options .= " AREA:doutbits#C3D9FF:";
   $options .= " LINE1.25:doutbits#000099:";
  } else {
   $options .= " AREA:inbits#CDEB8B:";
   $options .= " COMMENT:BPS\ \ \ \ Current\ \ \ Average\ \ \ \ \ \ Max\\\\n";
   $options .= " LINE1.25:inbits#006600:In\ ";
   $options .= " GPRINT:inbits:LAST:%6.2lf%s";
   $options .= " GPRINT:inbits:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:inbits:MAX:%6.2lf%s\\\\l";
   $options .= " AREA:doutbits#C3D9FF:";
   $options .= " LINE1.25:doutbits#000099:Out";
   $options .= " GPRINT:outbits:LAST:%6.2lf%s";
   $options .= " GPRINT:outbits:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:outbits:MAX:%6.2lf%s";
  }
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal"; }
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function temp_graph ($temp, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $options = " -l 0 -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") {
    $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal ";
  }
  $hostname = gethostbyid($device);
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $iter = "1";
  $sql = mysql_query("SELECT * FROM temperature where temp_id = '$temp'");
  $opts[] = "COMMENT:                                  Cur    Max";
  $temperature = mysql_fetch_array(mysql_query("SELECT * FROM temperature where temp_id = '$temp'"));
  $hostname = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '" . $temperature['temp_host'] . "'"),0);

  $temperature['temp_descr_fixed'] = str_pad($temperature['temp_descr'], 28);
  $temperature['temp_descr_fixed'] = substr($temperature['temp_descr_fixed'],0,28);
  
  $filename = str_replace(")", "_", str_replace("(", "_", str_replace("/", "_", str_replace(" ", "_",$temperature['temp_descr']))));

  $temprrd  = $config['rrd_dir'] . "/$hostname/temp-" . $filename . ".rrd";
  $opts[] = "DEF:temp=$temprrd:temp:AVERAGE";
  $opts[] = "CDEF:tempwarm=temp,".$temperature[temp_limit].",GT,temp,UNKN,IF";
  $opts[] = "LINE1.5:temp#006600:" . quotemeta($temperature[temp_descr_fixed]);
  $opts[] = "LINE1.5:tempwarm#cc0000";
  $opts[] = "GPRINT:temp:LAST:%3.0lf°C";
  $opts[] = "GPRINT:temp:MAX:%3.0lf°C\\\l";
  
  foreach($opts as $opt) {
    $opt = str_replace(" ","\ ", $opt);
    $options .= " $opt";
  }
#  echo($config['rrdtool'] . " graph $imgfile $options");
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_cpmCPU ($id, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $options  = "--start $from --end $to --width $width --height $height --vertical-label '$vertical' --alt-autoscale-max ";
  $options .= " -l 0 -E -b 1024 --title '$title' ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $hostname = gethostbyid($device);
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $iter = "1";
  $sql = mysql_query("SELECT * FROM `cpmCPU` AS C, `devices` AS D where C.`cpmCPU_id` = '$id' AND C.device_id = D.device_id");
  $options .= "COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ Cur\ \ \ \ Max\\\\n";
  while($proc = mysql_fetch_array($sql)) {
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }
    $proc['descr_fixed'] = str_pad($proc['entPhysicalDescr'], 28);
    $proc['descr_fixed'] = substr($proc['descr_fixed'],0,28);
    $rrd  = $config['rrd_dir'] . "/".$proc['hostname']."/cpmCPU-" . $proc['cpmCPU_oid'] . ".rrd";
    $options .= " DEF:proc" . $proc['cpmCPU_oid'] . "=$rrd:usage:AVERAGE ";
    $options .= " LINE1:proc" . $proc['cpmCPU_oid'] . "#" . $colour . ":'" . $proc['descr_fixed'] . "' ";
    $options .= " GPRINT:proc" . $proc['cpmCPU_oid'] . ":LAST:%3.0lf";
    $options .= " GPRINT:proc" . $proc['cpmCPU_oid'] . ":MAX:%3.0lf\\\l ";
    $iter++;
  }
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}



function graph_device_cpmCPU ($device, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $options  = "--start $from --end $to --width $width --height $height --vertical-label '$vertical' --alt-autoscale-max ";
  $options .= " -l 0 -E -b 1024 --title '$title' ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $hostname = gethostbyid($device);
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $iter = "1";
  $sql = mysql_query("SELECT * FROM `cpmCPU` where `device_id` = '$device'");
  $options .= "COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ Cur\ \ \ \ Max\\\\n";
  while($proc = mysql_fetch_array($sql)) {
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }
    $proc['descr_fixed'] = str_pad($proc['entPhysicalDescr'], 28);
    $proc['descr_fixed'] = substr($proc['descr_fixed'],0,28);
    $rrd  = $config['rrd_dir'] . "/$hostname/cpmCPU-" . $proc['cpmCPU_oid'] . ".rrd";
    $options .= " DEF:proc" . $proc['cpmCPU_oid'] . "=$rrd:usage:AVERAGE ";
    $options .= " LINE1:proc" . $proc['cpmCPU_oid'] . "#" . $colour . ":'" . $proc['descr_fixed'] . "' ";
    $options .= " GPRINT:proc" . $proc['cpmCPU_oid'] . ":LAST:%3.0lf";
    $options .= " GPRINT:proc" . $proc['cpmCPU_oid'] . ":MAX:%3.0lf\\\l ";
    $iter++;
  }
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_device_cempMemPool ($device, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $options  = "--start $from --end $to --width $width --height $height --vertical-label '$vertical' --alt-autoscale-max ";
  $options .= " -l 0 -E -b 1024 --title '$title' ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $hostname = gethostbyid($device);
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $iter = "1";
  $sql = mysql_query("SELECT * FROM `cempMemPool` where `device_id` = '$device'");
  $options .= "COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ Cur\ \ \ \ Max\\\\n";
  while($mempool = mysql_fetch_array($sql)) {
  $entPhysicalName = mysql_result(mysql_query("SELECT entPhysicalName from entPhysical WHERE device_id = '".$device."'
                                               AND entPhysicalIndex = '".$mempool['entPhysicalIndex']."'"),0);
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }
    $mempool['descr_fixed'] = $entPhysicalName . " " . $mempool['cempMemPoolName'];
    $mempool['descr_fixed'] = str_replace("Routing Processor", "RP", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_replace("Switching Processor", "SP", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_replace("Processor", "Proc", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_pad($mempool['descr_fixed'], 28);
    $mempool['descr_fixed'] = substr($mempool['descr_fixed'],0,28);
    $oid = $mempool['entPhysicalIndex'] . "." . $mempool['Index'];
    $rrd  = $config['rrd_dir'] . "/$hostname/cempMemPool-$oid.rrd";
    $id = $mempool['entPhysicalIndex'] . "-" . $mempool['Index'];
    $options .= " DEF:mempool" . $id . "free=$rrd:free:AVERAGE ";
    $options .= " DEF:mempool" . $id . "used=$rrd:used:AVERAGE ";
    $options .= " CDEF:mempool" . $id . "total=mempool" . $id . "used,mempool" . $id . "used,mempool" . $id . "free,+,/,100,* ";
    $options .= " LINE1:mempool" . $id . "total#" . $colour . ":'" . $mempool['descr_fixed'] . "' ";
    $options .= " GPRINT:mempool" . $id . "total:LAST:%3.0lf";
    $options .= " GPRINT:mempool" . $id . "total:MAX:%3.0lf\\\l ";
    $iter++;
  }
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_cempMemPool ($id, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $options  = "--start $from --end $to --width $width --height $height --vertical-label '$vertical' --alt-autoscale-max ";
  $options .= " -l 0 -E -b 1024 --title '$title' ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $hostname = gethostbyid($device);
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $iter = "1";
  $sql = mysql_query("SELECT * FROM `cempMemPool` AS C, `devices` AS D where C.`cempMemPool_id` = '$id' AND C.device_id = D.device_id");
  $options .= "COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ Cur\ \ \ \ Max\\\\n";
  while($mempool = mysql_fetch_array($sql)) {
  $entPhysicalName = mysql_result(mysql_query("SELECT entPhysicalName from entPhysical WHERE device_id = '".$mempool['device_id']."'
                                               AND entPhysicalIndex = '".$mempool['entPhysicalIndex']."'"),0);
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }
    $mempool['descr_fixed'] = $entPhysicalName . " " . $mempool['cempMemPoolName'];
    $mempool['descr_fixed'] = str_replace("Routing Processor", "RP", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_replace("Switching Processor", "SP", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_replace("Processor", "Proc", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_pad($mempool['descr_fixed'], 28);
    $mempool['descr_fixed'] = substr($mempool['descr_fixed'],0,28);
    $oid = $mempool['entPhysicalIndex'] . "." . $mempool['Index'];
    $rrd  = $config['rrd_dir'] . "/".$mempool['hostname']."/cempMemPool-$oid.rrd";
    $id = $mempool['entPhysicalIndex'] . "-" . $mempool['Index'];
    $options .= " DEF:mempool" . $id . "free=$rrd:free:AVERAGE ";
    $options .= " DEF:mempool" . $id . "used=$rrd:used:AVERAGE ";
    $options .= " CDEF:mempool" . $id . "total=mempool" . $id . "used,mempool" . $id . "used,mempool" . $id . "free,+,/,100,* ";
    $options .= " LINE1:mempool" . $id . "total#" . $colour . ":'" . $mempool['descr_fixed'] . "' ";
    $options .= " GPRINT:mempool" . $id . "total:LAST:%3.0lf";
    $options .= " GPRINT:mempool" . $id . "total:MAX:%3.0lf\\\l ";
    $iter++;
  }

  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}


function temp_graph_dev ($device, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $options  = "--start $from --end $to --width $width --height $height --vertical-label '$vertical' --alt-autoscale-max ";
  $options .= " -l 0 -E -b 1024 --title '$title' ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $hostname = gethostbyid($device);
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $iter = "1";
  $sql = mysql_query("SELECT * FROM temperature where temp_host = '$device'");
  $options .= "COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ Cur\ \ \ \ Max\\\\n";
  while($temperature = mysql_fetch_array($sql)) {
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }
    $temperature['temp_descr_fixed'] = str_pad($temperature['temp_descr'], 28);
    $temperature['temp_descr_fixed'] = substr($temperature['temp_descr_fixed'],0,28);
    $temprrd  = addslashes("rrd/$hostname/temp-" . str_replace("/", "_", str_replace(" ", "_",$temperature['temp_descr'])) . ".rrd");
    $temprrd  = str_replace(")", "_", $temprrd);
    $temprrd  = str_replace("(", "_", $temprrd);
    $options .= " DEF:temp" . $temperature[temp_id] . "=$temprrd:temp:AVERAGE ";
    $options .= " LINE1:temp" . $temperature[temp_id] . "#" . $colour . ":'" . $temperature[temp_descr_fixed] . "' ";
    $options .= " GPRINT:temp" . $temperature[temp_id] . ":LAST:%3.0lf\°C ";
    $options .= " GPRINT:temp" . $temperature[temp_id] . ":MAX:%3.0lf\°C\\\l ";
    $iter++;
  }
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_device_bits ($device, $graph, $from, $to, $width, $height, $title, $vertical, $inverse, $legend = '1') {
  global $config;
  $hostname = gethostbyid($device);
  $query = mysql_query("SELECT `ifIndex`,`interface_id` FROM `interfaces` WHERE `device_id` = '$device' AND `ifType` NOT LIKE '%oopback%' AND `ifType` NOT LIKE '%SVI%' AND `ifType` != 'l2vlan'");
  if($width <= "300") { $options .= "--font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $pluses = "";
  while($int = mysql_fetch_row($query)) {
    if(is_file($config['rrd_dir'] . "/" . $hostname . "/" . $int[0] . ".rrd")) {
      $interfaces .= $seperator . $int[1];
      $seperator = ",";
    }
  }
  $imgfile = graph_multi_bits($interfaces, $graph, $from, $to, $width, $height, $title, $vertical, $inverse, $legend);
  return $imgfile;
}

function graph_mac_acc ($id, $graph, $from, $to, $width, $height) {
  global $config;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $query = mysql_query("SELECT * FROM `mac_accounting` AS M, `interfaces` AS I, `devices` AS D WHERE M.ma_id = '".$id."' AND I.interface_id = M.interface_id AND I.device_id = D.device_id");
  $acc = mysql_fetch_array($query);
  $database = $config['rrd_dir'] . "/" . $acc['hostname'] . "/mac-accounting/" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd";  
  $options = "--alt-autoscale-max -E --start $from --end " . ($to - 150) . " --width $width --height $height ";
  if($height < "33") { $options .= " --only-graph"; }
  $period = $to - $from;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($height < "33") { $options .= " --only-graph"; }
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  
  $options .= " DEF:inoctets=$database:IN:AVERAGE";
  $options .= " DEF:outoctets=$database:OUT:AVERAGE";
  $options .= " CDEF:octets=inoctets,outoctets,+";
  $options .= " CDEF:doutoctets=outoctets,-1,*";
  $options .= " CDEF:inbits=inoctets,8,*";
  $options .= " CDEF:outbits=outoctets,8,*";
  $options .= " CDEF:doutbits=doutoctets,8,*";
  $options .= " VDEF:totin=inoctets,TOTAL";
  $options .= " VDEF:totout=outoctets,TOTAL";
  $options .= " VDEF:tot=octets,TOTAL";
  $options .= " VDEF:95thin=inbits,95,PERCENT";
  $options .= " VDEF:95thout=outbits,95,PERCENT";
  $options .= " VDEF:d95thout=doutbits,5,PERCENT";
  $options .= " AREA:inbits#CDEB8B:";
  $options .= " COMMENT:BPS\ \ \ \ Current\ \ \ Average\ \ \ \ \ \ Max\ \ \ 95th\ %\\\\n";
  $options .= " LINE1.25:inbits#006600:In\ ";
  $options .= " GPRINT:inbits:LAST:%6.2lf%s";
  $options .= " GPRINT:inbits:AVERAGE:%6.2lf%s";
  $options .= " GPRINT:inbits:MAX:%6.2lf%s";
  $options .= " GPRINT:95thin:%6.2lf%s\\\\n";
  $options .= " AREA:doutbits#C3D9FF:";
  $options .= " LINE1.25:doutbits#000099:Out";
  $options .= " GPRINT:outbits:LAST:%6.2lf%s";
  $options .= " GPRINT:outbits:AVERAGE:%6.2lf%s";
  $options .= " GPRINT:outbits:MAX:%6.2lf%s";
  $options .= " GPRINT:95thout:%6.2lf%s\\\\n";
  $options .= " GPRINT:tot:Total\ %6.2lf%s";
  $options .= " GPRINT:totin:\(In\ %6.2lf%s";
  $options .= " GPRINT:totout:Out\ %6.2lf%s\)\\\\l";
  $options .= " LINE1:95thin#aa0000";
  $options .= " LINE1:d95thout#aa0000";
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_mac_acc_interface ($interface, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -E --start $from --end " . ($to - 150) . " --width $width --height $height ";
  if($height < "33") { $options .= " --only-graph"; }
  $hostname = gethostbyid($device);
  $query = mysql_query("SELECT * FROM `mac_accounting` AS M, `interfaces` AS I, `devices` AS D WHERE M.interface_id = '$interface' AND I.interface_id = M.interface_id AND I.device_id = D.device_id");
  if($width <= "300") { $options .= "--font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $pluses = "";
  $options .= " COMMENT:'                                             In                    Out\\\\n'";
  while($acc = mysql_fetch_array($query)) {
   $this_rrd = $config['rrd_dir'] . "/" . $acc['hostname'] . "/mac-accounting/" . $acc['ifIndex'] . "-" . $acc['peer_ip'] . ".rrd";
   if(is_file($this_rrd)) { 
    $this_id = str_replace(".", "", $acc['peer_ip']);
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); } else {$colour="C600C6";}
    $descr = str_pad($acc['peer_desc'], 36);
    $descr = substr($descr,0,36);
    $options .= " DEF:in".$this_id."=$this_rrd:IN:AVERAGE ";
    $options .= " DEF:out".$this_id."temp=$this_rrd:OUT:AVERAGE ";
    $options .= " CDEF:out".$this_id."=out".$this_id."temp,-1,* ";
    $options .= " CDEF:octets".$this_id."=in".$this_id.",out".$this_id."temp,+";
    $options .= " VDEF:totin".$this_id."=in".$this_id.",TOTAL";
    $options .= " VDEF:totout".$this_id."=out".$this_id."temp,TOTAL";
    $options .= " VDEF:tot".$this_id."=octets".$this_id.",TOTAL";
    $options .= " LINE1.25:in".$this_id."#" . $colour . ":'" . $descr . "'";
    $options .= " LINE1.25:out".$this_id."#" . $colour . "::";
    $options .= " GPRINT:in".$this_id.":LAST:%6.2lf%sbps";
    $options .= " GPRINT:totin".$this_id.":\(%6.2lf%sB\)";
    $options .= " GPRINT:out".$this_id."temp:LAST:%6.2lf%sbps";
    $options .= " GPRINT:totout".$this_id.":\(%6.2lf%sB\)\\\\n";
    $iter++;
   }
  }
  #echo($config['rrdtool'] . " graph $imgfile $options");
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_bits ($rrd, $graph, $from, $to, $width, $height, $title, $vertical, $inverse = '0', $legend = '1') {
  global $config;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $period = $to - $from;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($height < "33") { $options .= " --only-graph"; unset ($legend); }
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  if($inverse) {
    $in = 'out';
    $out = 'in';
  } else {
    $in = 'in';
    $out = 'out';
  }
  $options .= " DEF:".$out."octets=$database:OUTOCTETS:AVERAGE";
  $options .= " DEF:".$in."octets=$database:INOCTETS:AVERAGE";
  $options .= " DEF:".$out."octets_max=$database:OUTOCTETS:MAX";
  $options .= " DEF:".$in."octets_max=$database:INOCTETS:MAX";

  $options .= " CDEF:octets=inoctets,outoctets,+";
  $options .= " CDEF:doutoctets=outoctets,-1,*";
  $options .= " CDEF:inbits=inoctets,8,*";
  $options .= " CDEF:inbits_max=inoctets_max,8,*";
  $options .= " CDEF:outbits_max=outoctets_max,8,*";
  $options .= " CDEF:doutoctets_max=outoctets_max,-1,*";
  $options .= " CDEF:doutbits_max=doutoctets_max,8,*";
  $options .= " CDEF:outbits=outoctets,8,*";
  $options .= " CDEF:doutbits=doutoctets,8,*";
  $options .= " VDEF:totin=inoctets,TOTAL";
  $options .= " VDEF:totout=outoctets,TOTAL";
  $options .= " VDEF:tot=octets,TOTAL";
  $options .= " VDEF:95thin=inbits,95,PERCENT";
  $options .= " VDEF:95thout=outbits,95,PERCENT";
  $options .= " VDEF:d95thout=doutbits,5,PERCENT";
  if ($legend == "no") {
    $options .= " AREA:inbits#CDEB8B";
    $options .= " LINE1.25:inbits#006600";
    $options .= " AREA:doutbits#C3D9FF";
    $options .= " LINE1.25:doutbits#000099";
    $options .= " LINE1:95thin#aa0000";
    $options .= " LINE1:d95thout#aa0000";
  } else {
    $options .= " AREA:inbits_max#aDEB7B:";
    $options .= " COMMENT:BPS\ \ \ \ Current\ \ \ Average\ \ \ \ \ \ Max\ \ \ 95th\ %\\\\n";
    $options .= " LINE1.25:inbits#006600:In\ ";
    $options .= " GPRINT:inbits:LAST:%6.2lf%s";
    $options .= " GPRINT:inbits:AVERAGE:%6.2lf%s";
    $options .= " GPRINT:inbits_max:MAX:%6.2lf%s";
    $options .= " GPRINT:95thin:%6.2lf%s\\\\n";
    $options .= " AREA:doutbits_max#a3b9FF:";
    $options .= " LINE1.25:doutbits#000099:Out";
    $options .= " GPRINT:outbits:LAST:%6.2lf%s";
    $options .= " GPRINT:outbits:AVERAGE:%6.2lf%s";
    $options .= " GPRINT:outbits_max:MAX:%6.2lf%s";
    $options .= " GPRINT:95thout:%6.2lf%s\\\\n";
    $options .= " GPRINT:tot:Total\ %6.2lf%s";
    $options .= " GPRINT:totin:\(In\ %6.2lf%s";
    $options .= " GPRINT:totout:Out\ %6.2lf%s\)\\\\l";
    $options .= " LINE1:95thin#aa0000";
    $options .= " LINE1:d95thout#aa0000";
  }
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function pktsgraph ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:in=$database:INUCASTPKTS:AVERAGE";
  $options .= " DEF:out=$database:OUTUCASTPKTS:AVERAGE";
  $options .= " CDEF:dout=out,-1,*";
  $options .= " AREA:in#aa66aa:";
  $options .= " COMMENT:Packets\ \ \ \ Current\ \ \ \ \ Average\ \ \ \ \ \ Maximum\\\\n";
  $options .= " LINE1.25:in#330033:In\ \ ";
  $options .= " GPRINT:in:LAST:%6.2lf%spps";
  $options .= " GPRINT:in:AVERAGE:%6.2lf%spps";
  $options .= " GPRINT:in:MAX:%6.2lf%spps\\\\n";
  $options .= " AREA:dout#FFDD88:";
  $options .= " LINE1.25:dout#FF6600:Out\ ";
  $options .= " GPRINT:out:LAST:%6.2lf%spps";
  $options .= " GPRINT:out:AVERAGE:%6.2lf%spps";
  $options .= " GPRINT:out:MAX:%6.2lf%spps\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function errorgraph ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:in=$database:INERRORS:AVERAGE";
  $options .= " DEF:out=$database:OUTERRORS:AVERAGE";
  $options .= " CDEF:dout=out,-1,*";
  $options .= " AREA:in#ff3300:";
  $options .= " COMMENT:Errors\ \ \ \ Current\ \ \ \ \ Average\ \ \ \ \ \ Maximum\\\\n";
  $options .= " LINE1.25:in#ff0000:In\ \ ";
  $options .= " GPRINT:in:LAST:%6.2lf%spps";
  $options .= " GPRINT:in:AVERAGE:%6.2lf%spps";
  $options .= " GPRINT:in:MAX:%6.2lf%spps\\\\n";
  $options .= " AREA:dout#FF6633:";
  $options .= " LINE1.25:dout#cc3300:Out\ ";
  $options .= " GPRINT:out:LAST:%6.2lf%spps";
  $options .= " GPRINT:out:AVERAGE:%6.2lf%spps";
  $options .= " GPRINT:out:MAX:%6.2lf%spps\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function nucastgraph ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:in=$database:INNUCASTPKTS:AVERAGE";
  $options .= " DEF:out=$database:OUTNUCASTPKTS:AVERAGE";
  $options .= " CDEF:dout=out,-1,*";
  $options .= " AREA:in#aa66aa:";
  $options .= " COMMENT:Packets\ \ \ \ Current\ \ \ \ \ Average\ \ \ \ \ \ Maximum\\\\n";
  $options .= " LINE1.25:in#330033:In\ \ ";
  $options .= " GPRINT:in:LAST:%6.2lf%spps";
  $options .= " GPRINT:in:AVERAGE:%6.2lf%spps";
  $options .= " GPRINT:in:MAX:%6.2lf%spps\\\\n";
  $options .= " AREA:dout#FFDD88:";
  $options .= " LINE1.25:dout#FF6600:Out\ ";
  $options .= " GPRINT:out:LAST:%6.2lf%spps";
  $options .= " GPRINT:out:AVERAGE:%6.2lf%spps";
  $options .= " GPRINT:out:MAX:%6.2lf%spps\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_cbgp_prefixes ($rrd, $graph, $from, $to, $width, $height) {
  global $config;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
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
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
#  echo($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}


function bgpupdatesgraph ($rrd, $graph , $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
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
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_cpu_generic_single ($rrd, $graph , $from, $to, $width, $height) {
  global $config;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -l 0 -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") {$options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:cpu=$database:cpu:AVERAGE";
  $options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ Current\ \ Minimum\ \ Maximum\ \ Average\\\\n";
  $options .= " AREA:cpu#ffee99: LINE1.25:cpu#aa2200:Load\ %";
  $options .= " GPRINT:cpu:LAST:%6.2lf\  GPRINT:cpu:AVERAGE:%6.2lf\ ";
  $options .= " GPRINT:cpu:MAX:%6.2lf\  GPRINT:cpu:AVERAGE:%6.2lf\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}


function graph_adsl_rate ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -l 0 -E --start $from --end $to --width $width --height $height ";
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
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_adsl_snr ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -l 0 -E --start $from --end $to --width $width --height $height ";
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
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_adsl_atn ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -l 0 -E --start $from --end $to --width $width --height $height ";
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
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function cpugraph ($rrd, $graph , $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -l 0 -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") {$options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:5s=$database:LOAD5S:AVERAGE";
  $options .= " DEF:5m=$database:LOAD5M:AVERAGE";
  $options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ Current\ \ Minimum\ \ Maximum\ \ Average\\\\n";
  $options .= " AREA:5m#ffee99: LINE1.25:5m#aa2200:Load\ %";
  $options .= " GPRINT:5m:LAST:%6.2lf\  GPRINT:5m:AVERAGE:%6.2lf\ ";
  $options .= " GPRINT:5m:MAX:%6.2lf\  GPRINT:5m:AVERAGE:%6.2lf\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function uptimegraph ($rrd, $graph , $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; } 
  $options .= " DEF:uptime=$database:uptime:AVERAGE";
  $options .= " CDEF:cuptime=uptime,86400,/";
  $options .= " COMMENT:Days\ \ \ \ \ \ Current\ \ Minimum\ \ Maximum\ \ Average\\\\n";
  $options .= " AREA:cuptime#EEEEEE:Uptime";
  $options .= " LINE1.25:cuptime#36393D:";
  $options .= " GPRINT:cuptime:LAST:%6.2lf\  GPRINT:cuptime:AVERAGE:%6.2lf\ ";
  $options .= " GPRINT:cuptime:MAX:%6.2lf\  GPRINT:cuptime:AVERAGE:%6.2lf\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}


function memgraph ($rrd, $graph , $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $period = $to - $from;
  $options = "-l 0 --alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
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
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function ip_graph ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $period = $to - $from;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:ipForwDatagrams=$database:ipForwDatagrams:AVERAGE";
  $options .= " DEF:ipInDelivers=$database:ipInDelivers:AVERAGE";
  $options .= " DEF:ipInReceives=$database:ipInReceives:AVERAGE";
  $options .= " DEF:ipOutRequests=$database:ipOutRequests:AVERAGE";
  $options .= " DEF:ipInDiscards=$database:ipInDiscards:AVERAGE";
  $options .= " DEF:ipOutDiscards=$database:ipOutDiscards:AVERAGE";
  $options .= " DEF:ipOutNoRoutes=$database:ipInDiscards:AVERAGE";
  $options .= " COMMENT:Packets/sec\ \ \ \ Current\ \ \ Average\ \ \ Maximum\\\n";
  $options .= " LINE1.25:ipForwDatagrams#cc0000:ForwDgrams\ ";
  $options .= " GPRINT:ipForwDatagrams:LAST:%6.2lf%s";
  $options .= " GPRINT:ipForwDatagrams:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:ipForwDatagrams:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:ipInDelivers#00cc00:InDelivers\ ";
  $options .= " GPRINT:ipInDelivers:LAST:%6.2lf%s";
  $options .= " GPRINT:ipInDelivers:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:ipInDelivers:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:ipInReceives#006600:InReceives\ ";
  $options .= " GPRINT:ipInReceives:LAST:%6.2lf%s";
  $options .= " GPRINT:ipInReceives:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:ipInReceives:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:ipOutRequests#0000cc:OutRequests";
  $options .= " GPRINT:ipOutRequests:LAST:%6.2lf%s";
  $options .= " GPRINT:ipOutRequests:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:ipOutRequests:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:ipInDiscards#cccc00:InDiscards\ ";
  $options .= " GPRINT:ipInDiscards:LAST:%6.2lf%s";
  $options .= " GPRINT:ipInDiscards:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:ipInDiscards:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:ipOutDiscards#330033:OutDiscards";
  $options .= " GPRINT:ipOutDiscards:LAST:%6.2lf%s";
  $options .= " GPRINT:ipOutDiscards:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:ipOutDiscards:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:ipOutNoRoutes#660000:OutNoRoutes";
  $options .= " GPRINT:ipOutNoRoutes:LAST:%6.2lf%s";
  $options .= " GPRINT:ipOutNoRoutes:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:ipOutNoRoutes:MAX:\ %6.2lf%s\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function icmp_graph ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $period = $to - $from;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }  $options .= "DEF:icmpInMsgs=$database:icmpInMsgs:AVERAGE";
  $options .= " DEF:icmpOutMsgs=$database:icmpOutMsgs:AVERAGE";
  $options .= " DEF:icmpInErrors=$database:icmpInErrors:AVERAGE";
  $options .= " DEF:icmpOutErrors=$database:icmpOutErrors:AVERAGE";
  $options .= " DEF:icmpInEchos=$database:icmpInEchos:AVERAGE";
  $options .= " DEF:icmpOutEchos=$database:icmpOutEchos:AVERAGE";
  $options .= " DEF:icmpInEchoReps=$database:icmpInEchoReps:AVERAGE";
  $options .= " DEF:icmpOutEchoReps=$database:icmpOutEchoReps:AVERAGE";
  $options .= " COMMENT:Packets/sec\ \ \ \ Current\ \ \ \ Average\ \ \ Maximum\\\\n";
  $options .= " LINE1.25:icmpInMsgs#00cc00:InMsgs ";
  $options .= " GPRINT:icmpInMsgs:LAST:\ \ \ \ \ %6.2lf%s";
  $options .= " GPRINT:icmpInMsgs:AVERAGE:\ \ %6.2lf%s";
  $options .= " GPRINT:icmpInMsgs:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:icmpOutMsgs#006600:OutMsgs    ";
  $options .= " GPRINT:icmpOutMsgs:LAST:\ \ \ \ %6.2lf%s";
  $options .= " GPRINT:icmpOutMsgs:AVERAGE:\ \ %6.2lf%s";
  $options .= " GPRINT:icmpOutMsgs:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:icmpInErrors#cc0000:InErrors   ";
  $options .= " GPRINT:icmpInErrors:LAST:\ \ \ %6.2lf%s";
  $options .= " GPRINT:icmpInErrors:AVERAGE:\ \ %6.2lf%s";
  $options .= " GPRINT:icmpInErrors:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:icmpOutErrors#660000:OutErrors  ";
  $options .= " GPRINT:icmpOutErrors:LAST:\ \ %6.2lf%s";
  $options .= " GPRINT:icmpOutErrors:AVERAGE:\ \ %6.2lf%s";
  $options .= " GPRINT:icmpOutErrors:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:icmpInEchos#0066cc:InEchos    ";
  $options .= " GPRINT:icmpInEchos:LAST:\ \ \ \ %6.2lf%s";
  $options .= " GPRINT:icmpInEchos:AVERAGE:\ \ %6.2lf%s";
  $options .= " GPRINT:icmpInEchos:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:icmpOutEchos#003399:OutEchos   ";
  $options .= " GPRINT:icmpOutEchos:LAST:\ \ \ %6.2lf%s";
  $options .= " GPRINT:icmpOutEchos:AVERAGE:\ \ %6.2lf%s";
  $options .= " GPRINT:icmpOutEchos:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:icmpInEchoReps#cc00cc:InEchoReps ";
  $options .= " GPRINT:icmpInEchoReps:LAST:\ %6.2lf%s";
  $options .= " GPRINT:icmpInEchoReps:AVERAGE:\ \ %6.2lf%s";
  $options .= " GPRINT:icmpInEchoReps:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:icmpOutEchoReps#990099:OutEchoReps";
  $options .= " GPRINT:icmpOutEchoReps:LAST:%6.2lf%s";
  $options .= " GPRINT:icmpOutEchoReps:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:icmpOutEchoReps:MAX:\ %6.2lf%s\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function tcp_graph ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $period = $to - $from;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }  $options .= "DEF:icmpInMsgs=$database:icmpInMsgs:AVERAGE";
  $options .= " DEF:tcpActiveOpens=$database:tcpActiveOpens:AVERAGE";
  $options .= " DEF:tcpPassiveOpens=$database:tcpPassiveOpens:AVERAGE";
  $options .= " DEF:tcpAttemptFails=$database:tcpAttemptFails:AVERAGE";
  $options .= " DEF:tcpEstabResets=$database:tcpEstabResets:AVERAGE";
  $options .= " DEF:tcpInSegs=$database:tcpInSegs:AVERAGE";
  $options .= " DEF:tcpOutSegs=$database:tcpOutSegs:AVERAGE";
  $options .= " DEF:tcpRetransSegs=$database:tcpRetransSegs:AVERAGE";
  $options .= " COMMENT:Packets/sec\ \ \ \ Current\ \ \ \ Average\ \ \ Maximum\\\n";
  $options .= " LINE1.25:tcpActiveOpens#00cc00:ActiveOpens\ ";
  $options .= " GPRINT:tcpActiveOpens:LAST:%6.2lf%s";
  $options .= " GPRINT:tcpActiveOpens:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:tcpActiveOpens:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:tcpPassiveOpens#006600:PassiveOpens";
  $options .= " GPRINT:tcpPassiveOpens:LAST:%6.2lf%s";
  $options .= " GPRINT:tcpPassiveOpens:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:tcpPassiveOpens:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:tcpAttemptFails#cc0000:AttemptFails";
  $options .= " GPRINT:tcpAttemptFails:LAST:%6.2lf%s";
  $options .= " GPRINT:tcpAttemptFails:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:tcpAttemptFails:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:tcpEstabResets#660000:EstabResets\ ";
  $options .= " GPRINT:tcpEstabResets:LAST:%6.2lf%s";
  $options .= " GPRINT:tcpEstabResets:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:tcpEstabResets:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:tcpInSegs#0066cc:InSegs\ \ \ \ \ \ ";
  $options .= " GPRINT:tcpInSegs:LAST:%6.2lf%s";
  $options .= " GPRINT:tcpInSegs:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:tcpInSegs:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:tcpOutSegs#003399:OutSegs\ \ \ \ \ ";
  $options .= " GPRINT:tcpOutSegs:LAST:%6.2lf%s";
  $options .= " GPRINT:tcpOutSegs:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:tcpOutSegs:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:tcpRetransSegs#cc00cc:RetransSegs\ ";
  $options .= " GPRINT:tcpRetransSegs:LAST:%6.2lf%s";
  $options .= " GPRINT:tcpRetransSegs:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:tcpRetransSegs:MAX:\ %6.2lf%s\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");  
  return $imgfile;
}

function udp_graph ($rrd, $graph, $from, $to, $width, $height) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $period = $to - $from;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }  $options .= "DEF:icmpInMsgs=$database:icmpInMsgs:AVERAGE";
  $options .= " DEF:udpInDatagrams=$database:udpInDatagrams:AVERAGE";
  $options .= " DEF:udpOutDatagrams=$database:udpOutDatagrams:AVERAGE";
  $options .= " DEF:udpInErrors=$database:udpInErrors:AVERAGE";
  $options .= " DEF:udpNoPorts=$database:udpNoPorts:AVERAGE";
  $options .= " COMMENT:Packets/sec\ \ \ \ Current\ \ \ \ Average\ \ \ Maximum\\\\n";
  $options .= " LINE1.25:udpInDatagrams#00cc00:InDatagrams\ ";
  $options .= " GPRINT:udpInDatagrams:LAST:%6.2lf%s";
  $options .= " GPRINT:udpInDatagrams:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:udpInDatagrams:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:udpOutDatagrams#006600:OutDatagrams";
  $options .= " GPRINT:udpOutDatagrams:LAST:%6.2lf%s";
  $options .= " GPRINT:udpOutDatagrams:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:udpOutDatagrams:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:udpInErrors#cc0000:InErrors\ \ \ \ ";
  $options .= " GPRINT:udpInErrors:LAST:%6.2lf%s";
  $options .= " GPRINT:udpInErrors:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:udpInErrors:MAX:\ %6.2lf%s\\\\n";
  $options .= " LINE1.25:udpNoPorts#660000:NoPorts\ \ \ \ \ ";
  $options .= " GPRINT:udpNoPorts:LAST:%6.2lf%s";
  $options .= " GPRINT:udpNoPorts:AVERAGE:\ %6.2lf%s";
  $options .= " GPRINT:udpNoPorts:MAX:\ %6.2lf%s\\\\n";
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}


?>
