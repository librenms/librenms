<?

function pollDeviceWin() {

   global $device;
   global $community;
   global $rrdtool;
   $id = $device['device_id'];
   $hostname = $device['hostname'];
   $hardware = $device['hardware'];
   $version = $device['version'];
   $features = $device['features'];

   $loadrrd  = "rrd/" . $hostname . "-load.rrd";
   $cpurrd   = "rrd/" . $hostname . "-cpu.rrd";
   $memrrd   = "rrd/" . $hostname . "-mem.rrd";
   $sysrrd   = "rrd/" . $hostname . "-sys.rrd";

   $oid_ssCpuRawUser         = ".1.3.6.1.4.1.2021.11.50.0";
   $oid_ssCpuRawSystem       = ".1.3.6.1.4.1.2021.11.51.0";
   $oid_ssCpuRawNice         = ".1.3.6.1.4.1.2021.11.52.0";
   $oid_ssCpuRawIdle         = ".1.3.6.1.4.1.2021.11.53.0";
   $oid_hrSystemProcesses    = ".1.3.6.1.2.1.25.1.6.0";
   $oid_hrSystemNumUsers     = ".1.3.6.1.2.1.25.1.5.0";

   $s_cmd  = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
   $s_cmd .= " $oid_ssCpuRawUser $oid_ssCpuRawSystem $oid_ssCpuRawNice $oid_ssCpuRawIdle $oid_hrSystemProcesses $oid_hrSystemNumUsers";
   $s      = `$s_cmd`; 
   list ($cpuUser, $cpuSystem, $cpuNice, $cpuIdle, $procs, $users) = explode("\n", $s);

   if (!is_file($cpurrd)) {
      `rrdtool create $cpurrd \
        --step 300 \
        DS:user:COUNTER:600:0:U \
        DS:system:COUNTER:600:0:U \
        DS:nice:COUNTER:600:0:U \
        DS:idle:COUNTER:600:0:U \
        RRA:AVERAGE:0.5:1:800 \
        RRA:AVERAGE:0.5:6:800 \
        RRA:AVERAGE:0.5:24:800 \
        RRA:AVERAGE:0.5:288:800 \
        RRA:MAX:0.5:1:800 \
        RRA:MAX:0.5:6:800 \
        RRA:MAX:0.5:24:800 \
        RRA:MAX:0.5:288:800`;
   }

   if (!is_file($sysrrd)) {
      `rrdtool create $sysrrd \
        --step 300 \
        DS:users:GAUGE:600:0:U \
        DS:procs:GAUGE:600:0:U \
        RRA:AVERAGE:0.5:1:800 \
        RRA:AVERAGE:0.5:6:800 \
        RRA:AVERAGE:0.5:24:800 \
        RRA:AVERAGE:0.5:288:800 \
        RRA:MAX:0.5:1:800 \
        RRA:MAX:0.5:6:800 \
        RRA:MAX:0.5:24:800 \
        RRA:MAX:0.5:288:800`;
   }

   if (!is_file($memrrd)) {
      `rrdtool create $memrrd \
       --step 300 \
       DS:totalswap:GAUGE:600:0:10000000000 \
       DS:availswap:GAUGE:600:0:10000000000 \
       DS:totalreal:GAUGE:600:0:10000000000 \
       DS:availreal:GAUGE:600:0:10000000000 \
       DS:totalfree:GAUGE:600:0:10000000000 \
       DS:shared:GAUGE:600:0:10000000000 \
       DS:buffered:GAUGE:600:0:10000000000 \
       DS:cached:GAUGE:600:0:10000000000 \
       RRA:AVERAGE:0.5:1:800 \
       RRA:AVERAGE:0.5:6:800 \
       RRA:AVERAGE:0.5:24:800 \
       RRA:AVERAGE:0.5:288:800 \
       RRA:MAX:0.5:1:800 \
       RRA:MAX:0.5:6:800 \
       RRA:MAX:0.5:24:800 \
       RRA:MAX:0.5:288:800`;
   }

   if(!is_file($loadrrd)) {
    `$rrdtool create $loadrrd \
    --step 300 \
    DS:1min:GAUGE:600:0:5000 \
    DS:5min:GAUGE:600:0:5000 \
    DS:15min:GAUGE:600:0:5000 \
    RRA:AVERAGE:0.5:1:800 \
    RRA:AVERAGE:0.5:6:800 \
    RRA:AVERAGE:0.5:24:800 \
    RRA:AVERAGE:0.5:288:800 \
    RRA:MAX:0.5:1:800 \
    RRA:MAX:0.5:6:800 \
    RRA:MAX:0.5:24:800 \
    RRA:MAX:0.5:288:800`;
   }

   $mem_get = "memTotalSwap.0 memAvailSwap.0 memTotalReal.0 memAvailReal.0 memTotalFree.0 memShared.0 memBuffer.0 memCached.0";
   $mem_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " " . $mem_get;
   $mem_raw = `$mem_cmd`;
   list($memTotalSwap, $memAvailSwap, $memTotalReal, $memAvailReal, $memTotalFree, $memShared, $memBuffer, $memCached) = explode("\n", $mem_raw); 

   $load_get = "laLoadInt.1 laLoadInt.2 laLoadInt.3";
   $load_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " " . $load_get;
   $load_raw = `$load_cmd`;
   list ($load1, $load5, $load10) = explode ("\n", $load_raw);

   rrd_update($sysrrd, "N:$users:$procs");
   rrd_update($loadrrd, "N:$load1:$load5:$load10");
   rrd_update($memrrd, "N:$memTotalSwap:$memAvailSwap:$memTotalReal:$memAvailReal:$memTotalFree:$memShared:$memBuffer:$memCached");
   rrd_update($cpurrd, "N:$cpuUser:$cpuSystem:$cpuNice:$cpuIdle");
}

function memgraphWin ($rrd, $graph, $from="-2d")
{
 global $rrdtool;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
    $opts = array ( 
    "--start",
    $from,
    "-v MB",
    "-b 1000", 
    "--rigid",
    "--title", "Memory Usage",
    "--alt-autoscale-max",
    "-l 0",
    "--width",  "335", "--height", "100",
    "DEF:totalswap=$database:totalswap:AVERAGE",
    "DEF:availswap=$database:availswap:AVERAGE",
    "DEF:totalreal=$database:totalreal:AVERAGE",
    "DEF:availreal=$database:availreal:AVERAGE",
    "DEF:totalfree=$database:totalfree:AVERAGE",
    "DEF:shared=$database:shared:AVERAGE",
    "DEF:buffered=$database:buffered:AVERAGE",
    "DEF:cached=$database:cached:AVERAGE",
    "CDEF:usedreal=totalreal,availreal,-",
    "CDEF:usedswap=totalswap,availswap,-",
    "CDEF:cdeftot=availreal,shared,buffered,usedreal,cached,usedswap,+,+,+,+,+",
    "AREA:usedreal#ee8000:used",
    "GPRINT:usedreal:LAST:   Cur\:%8.2lf %s",
    "GPRINT:usedreal:AVERAGE: Avg\:%8.2lf %s",
    "GPRINT:usedreal:MAX: Max\:%8.2lf %s\\n",
    "STACK:shared#ec9900:shared",
    "GPRINT:shared:LAST: Cur\:%8.2lf %s",
    "GPRINT:shared:AVERAGE: Avg\:%8.2lf %s",
    "GPRINT:shared:MAX: Max\:%8.2lf %s\\n",
    "STACK:availreal#eacc00:free",
    "GPRINT:availreal:LAST:   Cur\:%8.2lf %s",
    "GPRINT:availreal:AVERAGE: Avg\:%8.2lf %s",
    "GPRINT:availreal:MAX: Max\:%8.2lf %s\\n",
    "STACK:buffered#cc0000:buffers",
    "GPRINT:buffered:LAST:Cur\:%8.2lf %s",
    "GPRINT:buffered:AVERAGE: Avg\:%8.2lf %s",
    "GPRINT:buffered:MAX: Max\:%8.2lf %s\\n",
    "STACK:cached#9fa4ee:cached",
    "GPRINT:cached:LAST: Cur\:%8.2lf %s",
    "GPRINT:cached:AVERAGE: Avg\:%8.2lf %s",
    "GPRINT:cached:MAX: Max\:%8.2lf %s\\n",
    "STACK:usedswap#afeced:swap",
    "GPRINT:usedswap:LAST:   Cur\:%8.2lf %s",
    "GPRINT:usedswap:AVERAGE: Avg\:%8.2lf %s",
    "GPRINT:usedswap:MAX: Max\:%8.2lf %s",
    "LINE1:totalreal#050505:total");
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error();
    echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}

function loadgraphWin ($rrd, $graph, $from="-2d") {
    global $rrdtool;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
    $opts = array(
    "--title", "Load Averages",
    "--start",
    $from,
    "-v Load",
    "--rigid",
    "--alt-autoscale-max",
    "-l 0",
    "--width", "335", "--height", "100",
    "DEF:1min=$database:1min:AVERAGE",
    "DEF:5min=$database:5min:AVERAGE",
    "DEF:15min=$database:15min:AVERAGE",
    "CDEF:a=1min,100,/",
    "CDEF:b=5min,100,/",
    "CDEF:c=15min,100,/",
    "CDEF:cdefd=a,b,c,+,+",
    "AREA:a#eacc00:1 Minute:",
    "LINE1:a#c5aa00:",
    "GPRINT:a:LAST: Cur\:%8.2lf %s",
    "GPRINT:a:AVERAGE: Ave\:%8.2lf %s",
    "GPRINT:a:MAX: Max\:%8.2lf %s\\n",
    "LINE1.5:b#ea8f00:5 Minute:",
    "GPRINT:b:LAST: Cur\:%8.2lf %s",
    "GPRINT:b:AVERAGE: Ave\:%8.2lf %s",
    "GPRINT:b:MAX: Max\:%8.2lf %s\\n",
    "LINE1.5:c#cc0000:15 Minute",
    "GPRINT:c:LAST:Cur\:%8.2lf %s",
    "GPRINT:c:AVERAGE: Ave\:%8.2lf %s",
    "GPRINT:c:MAX: Max\:%8.2lf %s\\n");

    $ret = rrd_graph("$imgfile", $opts, count($opts));
    if( !is_array($ret) ) {
       $err = rrd_error();
       echo "rrd_graph() ERROR: $err\n";
       return FALSE;
    } else {
       return $imgfile;
    }
}

function usersgraphWin ($rrd, $graph, $from="-2d") {
  global $rrdtool;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $opts = array(
    "--title", "Logged on Users",
    "--vertical-label", "Users",
    "-l 0",
    "--width", "335", "--height",  "100",
    "--start",
    $from,
    "DEF:users=$database:users:AVERAGE",
    "AREA:users#eacc00:users",
    "LINE1.5:users#cc0000:",
    "GPRINT:users:LAST:  Cur\:%3.0lf %s",
    "GPRINT:users:AVERAGE: Avg\:%3.0lf %s",
    "GPRINT:users:MIN: Min\:%3.0lf %s",
    "GPRINT:users:MAX: Max\:%3.0lf %s\\n");

    $ret = rrd_graph("$imgfile", $opts, count($opts));
    if( !is_array($ret) ) {
       $err = rrd_error();
       echo "rrd_graph() ERROR: $err\n";
       return FALSE;
    } else {
       return $imgfile;
    }
}

function procsgraphWin ($rrd, $graph, $from="-2d") {
  global $rrdtool;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $opts = array(
    "-v # Processes",
    "--title", "Running Processes",
    "--vertical-label", "procs",
    "-l 0",
    "--width", "335", "--height",  "100",
    "--start",
    $from,
    "DEF:procs=$database:procs:AVERAGE",
    "AREA:procs#eacc00:Processes",
   "LINE1.5:procs#cc0000:",
    "GPRINT:procs:LAST:  Cur\:%3.0lf %s",
    "GPRINT:procs:AVERAGE: Avg\:%3.0lf %s",
    "GPRINT:procs:MIN: Min\:%3.0lf %s",
    "GPRINT:procs:MAX: Max\:%3.0lf %s\\n");

    $ret = rrd_graph("$imgfile", $opts, count($opts));
    if( !is_array($ret) ) {
       $err = rrd_error();
       echo "rrd_graph() ERROR: $err\n";
       return FALSE;
    } else {
       return $imgfile;
    }
}

function cpugraphWin ($rrd, $graph, $from="-2d") {
  global $rrdtool;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $opts = array(
    "-v CPU Utilization",
    "--title", "Processor Usage",
    "-u 100",
    "--rigid",
    "--vertical-label", "Load (%)",
    "-l 0",
    "--width", "335", "--height",  "100",
    "--start",
    $from,
    "DEF:user=$database:user:AVERAGE",
    "DEF:nice=$database:nice:AVERAGE",
    "DEF:system=$database:system:AVERAGE",
    "DEF:idle=$database:idle:AVERAGE",
    "CDEF:total=user,nice,system,idle,+,+,+",
    "CDEF:user_perc=user,total,/,100,*",
    "CDEF:nice_perc=nice,total,/,100,*",
    "CDEF:system_perc=system,total,/,100,*",
    "CDEF:idle_perc=idle,total,/,100,*",
    "AREA:user_perc#eacc00:user",
    "GPRINT:user_perc:LAST:  Cur\:%3.0lf%%",
    "GPRINT:user_perc:AVERAGE: Avg\:%3.0lf%%",
    "GPRINT:user_perc:MAX: Max\:%3.0lf%%\\n",
    "AREA:nice_perc#ea8f00:system:STACK",
    "GPRINT:nice_perc:LAST:Cur\:%3.0lf%%",
    "GPRINT:nice_perc:AVERAGE: Avg\:%3.0lf%%",
    "GPRINT:nice_perc:MAX: Max\:%3.0lf%%\\n",
    "AREA:system_perc#ff3932:nice:STACK",
    "GPRINT:system_perc:LAST:  Cur\:%3.0lf%%",
    "GPRINT:system_perc:AVERAGE: Avg\:%3.0lf%%",
    "GPRINT:system_perc:MAX: Max\:%3.0lf%%\\n",
    "AREA:idle_perc#fafdce:idle:STACK",
    "GPRINT:idle_perc:LAST:  Cur\:%3.0lf%%",
    "GPRINT:idle_perc:AVERAGE: Avg\:%3.0lf%%",
    "GPRINT:idle_perc:MAX: Max\:%3.0lf%%\\n");
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error();
    return FALSE;
  } else {
    return $imgfile;
  }
}

function storagegraphWin ($rrd, $graph, $from="-2d", $descr)
{
 global $rrdtool;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
    $opts = array (
    "--start",
    $from,
    "-v MB",
    "-b 1024",
    "--rigid",
    "--title", $descr,
    "--alt-autoscale-max",
    "-l 0",
    "--width",  "335", "--height", "100",
    "DEF:size=$database:size:AVERAGE",
    "DEF:used=$database:used:AVERAGE",
    "AREA:size#80ee80:Total",
    "GPRINT:size:LAST:Cur\:%8.2lf %s",
    "GPRINT:size:AVERAGE: Avg\:%8.2lf %s",
    "GPRINT:size:MAX: Max\:%8.2lf %s\\n",
    "AREA:used#ec9900:Used",
    "GPRINT:used:LAST: Cur\:%8.2lf %s",
    "GPRINT:used:AVERAGE: Avg\:%8.2lf %s",
    "GPRINT:used:MAX: Max\:%8.2lf %s\\n",
    "LINE1:size#000000:");
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
