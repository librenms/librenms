<?php

$loadrrd  = $host_rrd . "/ucd_load.rrd";
$cpurrd   = $host_rrd . "/ucd_cpu.rrd";
$memrrd   = $host_rrd . "/ucd_mem.rrd";

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

rrdtool_update($cpurrd,  "N:".($cpuUser+0).":".($cpuSystem+0).":".($cpuNice+0).":".($cpuIdle+0));


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
  rrdtool_update($memrrd,  "N:$memTotalSwap:$memAvailSwap:$memTotalReal:$memAvailReal:$memTotalFree:".($memShared+0).":".($memBuffer+0).":".($memCached+0));
