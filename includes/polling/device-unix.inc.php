<?php

$loadrrd  = "rrd/" . $device['hostname'] . "-load.rrd";
$cpurrd   = "rrd/" . $device['hostname'] . "-cpu.rrd";
$memrrd   = "rrd/" . $device['hostname'] . "-mem.rrd";
$sysrrd   = "rrd/" . $device['hostname'] . "-sys.rrd";

## Check Disks
$dq = mysql_query("SELECT * FROM storage WHERE host_id = '" . $device['device_id'] . "'");
while ($dr = mysql_fetch_array($dq)) {
  $hrStorageIndex = $dr['hrStorageIndex'];
  $hrStorageAllocationUnits = $dr['hrStorageAllocationUnits'];
  $hrStorageSize = $dr['hrStorageAllocationUnits'] * $dr['hrStorageSize']; 
  $hrStorageDescr = $dr['hrStorageDescr'];
  $cmd  = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " hrStorageUsed.$hrStorageIndex";
  $used_units = trim(`$cmd`);
  $used = $used_units * $hrStorageAllocationUnits;
  $perc = round($used / $hrStorageSize * 100, 2);

  $filedesc = str_replace("\"", "", str_replace("/", "_", $hrStorageDescr));
  $storerrd  = "rrd/" . $device['hostname'] . "-storage-" . $filedesc . ".rrd";
  if (!is_file($storerrd)) {
    `rrdtool create $storerrd \
     --step 300 \
     DS:size:GAUGE:600:0:U \
     DS:used:GAUGE:600:0:U \
     DS:perc:GAUGE:600:0:U \
     RRA:AVERAGE:0.5:1:800 \
     RRA:AVERAGE:0.5:6:800 \
     RRA:AVERAGE:0.5:24:800 \
     RRA:AVERAGE:0.5:288:800 \
     RRA:MAX:0.5:1:800 \
     RRA:MAX:0.5:6:800 \
     RRA:MAX:0.5:24:800 \
     RRA:MAX:0.5:288:800`;
  }  
  rrdtool_update($storerrd, "N:$hrStorageSize:$used:$perc");
  mysql_query("UPDATE `storage` SET `hrStorageUsed` = '$used_units', `storage_perc` = '$perc' WHERE storage_id = '" . $dr['storage_id'] . "'");

    if($dr['storage_perc'] < '40' && $perc >= '40') {

    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }

    $msg  = "Disk Alarm: " . $device['hostname'] . " " . $dr['hrStorageDescr'] . " is " . $perc;
    $msg .= " at " . date('l dS F Y h:i:s A');

    mail($email, "Disk Alarm: " . $device['hostname'] . " " . $dr['hrStorageDescr'], $msg, $config['email_headers']);

    echo("Alerting for " . $device['hostname'] . " " . $dr['hrStorageDescr'] . "/n");

  }


}

## Set OIDs
$oid_ssCpuRawUser         = ".1.3.6.1.4.1.2021.11.50.0";
$oid_ssCpuRawNice         = ".1.3.6.1.4.1.2021.11.51.0";
$oid_ssCpuRawSystem       = ".1.3.6.1.4.1.2021.11.52.0";
$oid_ssCpuRawIdle         = ".1.3.6.1.4.1.2021.11.53.0";

$oid_hrSystemProcesses    = ".1.3.6.1.2.1.25.1.6.0";
$oid_hrSystemNumUsers     = ".1.3.6.1.2.1.25.1.5.0";

$oid_ssCpuUser		  = ".1.3.6.1.4.1.2021.11.9.0";
$oid_ssCpuSystem	  = ".1.3.6.1.4.1.2021.11.10.0";


$cpu_cmd  = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
$cpu_cmd .= " $oid_ssCpuRawUser $oid_ssCpuRawSystem $oid_ssCpuRawNice $oid_ssCpuRawIdle $oid_hrSystemProcesses";
$cpu_cmd .= " $oid_hrSystemNumUsers $oid_ssCpuUser $oid_ssCpuSystem .1.3.6.1.4.1.2021.1.101.1";
$cpu  = `$cpu_cmd`;
list ($cpuUser, $cpuSystem, $cpuNice, $cpuIdle, $procs, $users, $UsageUser, $UsageSystem, $cputemp) = explode("\n", $cpu);

$cpuUsage = $usageUser + $usageSystem;

$update_usage = mysql_query("UPDATE devices_attribs SET attrib_value = '$cpuUsage' WHERE `device_id` = '" . $device['device_id'] . "' AND `attrib_type` = 'cpuusage'");

if(mysql_affected_rows() == '0') {
 $insert_usage = mysql_query("INSERT INTO devices_attribs (`device_id`, `attrib_type`, `attrib_value`) VALUES ('" . $device['device_id'] . "', 'cpuusage', '$cpuUsage')");
}

## Create CPU RRD if it doesn't already exist
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
rrdtool_update($cpurrd,  "N:$cpuUser:$cpuSystem:$cpuNice:$cpuIdle");


## If the device isn't monowall or pfsense, monitor all the pretty things
if($device[os] != "m0n0wall" && $device[os] != "Voswall" && $device[os] != "pfSense" ) {
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
  } // end create mem rrd

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
  } // end create load rrd

  $mem_get = "memTotalSwap.0 memAvailSwap.0 memTotalReal.0 memAvailReal.0 memTotalFree.0 memShared.0 memBuffer.0 memCached.0";
  $mem_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " " . $mem_get;
  $mem_raw = `$mem_cmd`;
  list($memTotalSwap, $memAvailSwap, $memTotalReal, $memAvailReal, $memTotalFree, $memShared, $memBuffer, $memCached) = explode("\n", $mem_raw); 

  $load_get = "laLoadInt.1 laLoadInt.2 laLoadInt.3";
  $load_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " " . $load_get;
  $load_raw = `$load_cmd`;
  list ($load1, $load5, $load10) = explode ("\n", $load_raw);

  rrdtool_update($sysrrd,  "N:$users:$procs");
  rrdtool_update($loadrrd, "N:$load1:$load5:$load10");
  rrdtool_update($memrrd,  "N:$memTotalSwap:$memAvailSwap:$memTotalReal:$memAvailReal:$memTotalFree:$memShared:$memBuffer:$memCached");

  if($device['courier']) {
    include("includes/polling/courierstats.inc.php");
  }

  if($device['postfix']) {
    include("includes/polling/mailstats.inc.php");
  }

  if($device['apache']) {
   include("includes/polling/apachestats.inc.php");
  }

} // end Non-m0n0wall
