<?php

$loadrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/load.rrd";
$cpurrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/cpu.rrd";
$memrrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/mem.rrd";

      if ($device['os'] == "FreeBSD") {
        $sysDescr = str_replace(" 0 ", " ", $sysDescr);
        list(,,$version) = explode (" ", $sysDescr);
        $hardware = "i386";
        $features = "GENERIC";
      } elseif ($device['os'] == "DragonFly") {
        list(,,$version,,,$features,,$hardware) = explode (" ", $sysDescr);
      } elseif ($device['os'] == "NetBSD") {
        list(,,$version,,,$features) = explode (" ", $sysDescr);
        $features = str_replace("(", "", $features);
        $features = str_replace(")", "", $features);
        list(,,$hardware) = explode ("$features", $sysDescr);
      } elseif ($device['os'] == "OpenBSD") {
        list(,,$version,$features,$hardware) = explode (" ", $sysDescr);
        $features = str_replace("(", "", $features);
        $features = str_replace(")", "", $features);
      } elseif ($device['os'] == "m0n0wall" || $device['os'] == "Voswall") {
        list(,,$version,$hardware,$freebsda, $freebsdb, $arch) = split(" ", $sysDescr);
        $features = $freebsda . " " . $freebsdb;
        $hardware = "$hardware ($arch)";
        $hardware = str_replace("\"", "", $hardware);
      } elseif ($device['os'] == "Linux") {
        list(,,$version) = explode (" ", $sysDescr);
        if(strstr($sysDescr, "386")|| strstr($sysDescr, "486")||strstr($sysDescr, "586")||strstr($sysDescr, "686")) { $hardware = "Generic x86"; }
        if(strstr($sysDescr, "x86_64")) { $hardware = "Generic x86 64-bit"; }
        $cmd = $config['snmpget'] . " -m UCD-SNMP-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port']. " .1.3.6.1.4.1.2021.7890.1.3.1.1.6.100.105.115.116.114.111";
        $features = trim(`$cmd`);
        $features = str_replace("No Such Object available on this agent at this OID", "", $features);
        $features = str_replace("\"", "", $features);
        // Detect Dell hardware via OpenManage SNMP
        $cmd = $config['snmpget'] . " -m MIB-Dell-10892 -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " .1.3.6.1.4.1.674.10892.1.300.10.1.9.1";
        $hw = trim(str_replace("\"", "", `$cmd`));
        if(strstr($hw, "No")) { unset($hw); } else { $hardware = "Dell " . $hw; }
      }


## Set OIDs
$oid_ssCpuRawUser         = ".1.3.6.1.4.1.2021.11.50.0";
$oid_ssCpuRawNice         = ".1.3.6.1.4.1.2021.11.51.0";
$oid_ssCpuRawSystem       = ".1.3.6.1.4.1.2021.11.52.0";
$oid_ssCpuRawIdle         = ".1.3.6.1.4.1.2021.11.53.0";
$oid_ssCpuUser		  = ".1.3.6.1.4.1.2021.11.9.0";
$oid_ssCpuSystem	  = ".1.3.6.1.4.1.2021.11.10.0";

$cpu_cmd  = $config['snmpget'] ." -m UCD-SNMP-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
$cpu_cmd .= " $oid_ssCpuRawUser $oid_ssCpuRawSystem $oid_ssCpuRawNice $oid_ssCpuRawIdle $oid_ssCpuUser $oid_ssCpuSystem";
$cpu  = `$cpu_cmd`;
list ($cpuUser, $cpuSystem, $cpuNice, $cpuIdle, $UsageUser, $UsageSystem) = explode("\n", $cpu);

$cpuUsage = $UsageUser + $UsageSystem;

if(mysql_result(mysql_query("SELECT COUNT(*) FROM devices_attribs WHERE `device_id` = '" . $device['device_id'] . "' AND `attrib_type` = 'cpuusage'"),0)) {
 $update_usage = mysql_query("UPDATE devices_attribs SET attrib_value = '$cpuUsage' WHERE `device_id` = '" . $device['device_id'] . "' AND `attrib_type` = 'cpuusage'");
} else {
 $insert_usage = mysql_query("INSERT INTO devices_attribs (`device_id`, `attrib_type`, `attrib_value`) VALUES ('" . $device['device_id'] . "', 'cpuusage', '$cpuUsage')");
}

## Create CPU RRD if it doesn't already exist
if (!is_file($cpurrd)) {
   shell_exec($config['rrdtool'] . " create $cpurrd \
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
     RRA:MAX:0.5:288:800");
}
rrdtool_update($cpurrd,  "N:$cpuUser:$cpuSystem:$cpuNice:$cpuIdle");


## If the device isn't monowall or pfsense, monitor all the pretty things
if($device[os] != "m0n0wall" && $device[os] != "Voswall" && $device[os] != "pfSense" ) {
  if (!is_file($memrrd)) {
      shell_exec($config['rrdtool'] . " create $memrrd \
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
       RRA:MAX:0.5:288:800");
  } // end create mem rrd

   if(!is_file($loadrrd)) {
    shell_exec($config['rrdtool'] . " create $loadrrd \
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
    RRA:MAX:0.5:288:800");
  } // end create load rrd

  $mem_cmd  = $config['snmpget'] . " -m UCD-SNMP-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
  $mem_cmd .= " memTotalSwap.0 memAvailSwap.0 memTotalReal.0 memAvailReal.0 memTotalFree.0 memShared.0 memBuffer.0 memCached.0";

  $mem_raw = shell_exec($mem_cmd);
  list($memTotalSwap, $memAvailSwap, $memTotalReal, $memAvailReal, $memTotalFree, $memShared, $memBuffer, $memCached) = explode("\n", str_replace(" kB", "", $mem_raw)); 

  $load_get = "laLoadInt.1 laLoadInt.2 laLoadInt.3";
  $load_cmd = "snmpget -m UCD-SNMP-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " " . $load_get;
  $load_raw = `$load_cmd`;
  list ($load1, $load5, $load10) = explode ("\n", $load_raw);

  rrdtool_update($loadrrd, "N:$load1:$load5:$load10");
  rrdtool_update($memrrd,  "N:$memTotalSwap:$memAvailSwap:$memTotalReal:$memAvailReal:$memTotalFree:$memShared:$memBuffer:$memCached");

} // end Non-m0n0wall

