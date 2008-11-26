<?

   if(strstr($sysDescr, "x86")) { $hardware = "Generic x86"; }
   if(strstr($sysDescr, "Windows Version 5.2")) { $version = "2003 Server"; }
   if(strstr($sysDescr, "Uniprocessor Free")) { $features = "Uniprocessor"; }
   if(strstr($sysDescr, "Multiprocessor Free")) { $features = "Multiprocessor"; }

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

   if (!is_file($sysrrd)) {
      shell_exec($config['rrdtool'] . " create $sysrrd \
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
        RRA:MAX:0.5:288:800");
   }

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
   }

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
   }

   $mem_get = "memTotalSwap.0 memAvailSwap.0 memTotalReal.0 memAvailReal.0 memTotalFree.0 memShared.0 memBuffer.0 memCached.0";
   $mem_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " " . $mem_get;
   $mem_raw = `$mem_cmd`;
   list($memTotalSwap, $memAvailSwap, $memTotalReal, $memAvailReal, $memTotalFree, $memShared, $memBuffer, $memCached) = explode("\n", $mem_raw); 

   $load_get = "laLoadInt.1 laLoadInt.2 laLoadInt.3";
   $load_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " " . $load_get;
   $load_raw = `$load_cmd`;
   list ($load1, $load5, $load10) = explode ("\n", $load_raw);

   rrdtool_update($sysrrd, "N:$users:$procs");
   rrdtool_update($loadrrd, "N:$load1:$load5:$load10");
   rrdtool_update($memrrd, "N:$memTotalSwap:$memAvailSwap:$memTotalReal:$memAvailReal:$memTotalFree:$memShared:$memBuffer:$memCached");
   rrdtool_update($cpurrd, "N:$cpuUser:$cpuSystem:$cpuNice:$cpuIdle");

?>
