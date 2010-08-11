<?php

  $load_rrd  = $host_rrd . "/ucd_load.rrd";
  $cpu_rrd   = $host_rrd . "/ucd_cpu.rrd";
  $mem_rrd   = $host_rrd . "/ucd_mem.rrd";

  ### Poll systemStats from UNIX-like hosts running UCD/Net-SNMPd

  #UCD-SNMP-MIB::ssIndex.0 = INTEGER: 1
  #UCD-SNMP-MIB::ssErrorName.0 = STRING: systemStats
  #UCD-SNMP-MIB::ssSwapIn.0 = INTEGER: 0 kB
  #UCD-SNMP-MIB::ssSwapOut.0 = INTEGER: 0 kB
  #UCD-SNMP-MIB::ssIOSent.0 = INTEGER: 1864 blocks/s
  #UCD-SNMP-MIB::ssIOReceive.0 = INTEGER: 7 blocks/s
  #UCD-SNMP-MIB::ssSysInterrupts.0 = INTEGER: 7572 interrupts/s
  #UCD-SNMP-MIB::ssSysContext.0 = INTEGER: 10254 switches/s
  #UCD-SNMP-MIB::ssCpuUser.0 = INTEGER: 4
  #UCD-SNMP-MIB::ssCpuSystem.0 = INTEGER: 3
  #UCD-SNMP-MIB::ssCpuIdle.0 = INTEGER: 77
  #UCD-SNMP-MIB::ssCpuRawUser.0 = Counter32: 194386556
  #UCD-SNMP-MIB::ssCpuRawNice.0 = Counter32: 15673
  #UCD-SNMP-MIB::ssCpuRawSystem.0 = Counter32: 65382910
  #UCD-SNMP-MIB::ssCpuRawIdle.0 = Counter32: 1655192684
  #UCD-SNMP-MIB::ssCpuRawWait.0 = Counter32: 205336019
  #UCD-SNMP-MIB::ssCpuRawKernel.0 = Counter32: 0
  #UCD-SNMP-MIB::ssCpuRawInterrupt.0 = Counter32: 1128048
  #UCD-SNMP-MIB::ssIORawSent.0 = Counter32: 2353983704
  #UCD-SNMP-MIB::ssIORawReceived.0 = Counter32: 3172182750
  #UCD-SNMP-MIB::ssRawInterrupts.0 = Counter32: 427446276
  #UCD-SNMP-MIB::ssRawContexts.0 = Counter32: 4161026807
  #UCD-SNMP-MIB::ssCpuRawSoftIRQ.0 = Counter32: 2605010
  #UCD-SNMP-MIB::ssRawSwapIn.0 = Counter32: 602002
  #UCD-SNMP-MIB::ssRawSwapOut.0 = Counter32: 937422

  $ss = snmpwalk_cache_oid($device, "systemStats", array());

  ## Create CPU RRD if it doesn't already exist
  $cpu_rrd_create = " --step 300 \
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
     RRA:MAX:0.5:288:800";
 
  ### This is how we currently collect. We should collect one RRD per stat, for ease of handling differen formats,
  ### and because it is per-host and no big performance hit. See new format below
  ### FIXME REMOVE

  if(is_numeric($ss['ssCpuRawUser']) && is_numeric($ss['ssCpuRawNice']) && is_numeric($ss['ssCpuRawSystem']) && is_numeric($ss['ssCpuRawIdle'])) 
  {
    if (!is_file($cpu_rrd)) 
    {
      rrdtool_create($cpu_rrd, $cpu_rrd_create);
    }
    rrdtool_update($cpu_rrd,  "N:".$ss['ssCpuRawUser'].":".$ss['ssCpuRawSystem'].":".$ss['ssCpuRawNice'].":".$ss['ssCpuRawIdle']);
  }


  ### This is how we'll collect in the future, start now so people don't have zero data.

  $collect_oids = array('ssCpuRawUser','ssCpuRawNice','ssCpuRawSystem','ssCpuRawIdle','ssCpuRawInterrupt', 'ssCpuRawSoftIRQ', 'ssCpuRawKernel', 'ssCpuRawWait', 'ssIORawSent', 'ssIORawReceived', 'ssRawInterrupts', 'ssRawContexts', 'ssRawSwapIn', 'ssRawSwapOut');

  foreach($collect_oids as $oid)
  {
    if(is_numeric($ss[$oid])) 
    {
      $value = $ss[$oid];
      $filename = $host_rrd . "/ucd_".$oid.".rrd";
      if(!is_file($filename)) 
      {
        rrdtool_create($filename, " --step 300 DS:value:COUNTER:600:0:U RRA:AVERAGE:0.5:1:800 RRA:AVERAGE:0.5:6:800 RRA:AVERAGE:0.5:24:800 RRA:AVERAGE:0.5:288:800 RRA:MAX:0.5:1:800 RRA:MAX:0.5:6:800 RRA:MAX:0.5:24:800 RRA:MAX:0.5:288:800");
      }
      rrdtool_update($filename, "N:".$value);
      $graphs['ucd_cpu'] = TRUE;
    }
  }

  ### Set various graphs if we've seen the right OIDs.

  if(is_numeric($ss['ssRawSwapIn'])) { $graphs['ucd_swap_io'] = TRUE; }
  if(is_numeric($ss['ssIORawSent'])) { $graphs['ucd_io'] = TRUE; }
  if(is_numeric($ss['ssRawContexts'])) { $graphs['ucd_contexts'] = TRUE; }
  if(is_numeric($ss['ssRawInterrupts'])) { $graphs['ucd_interrupts'] = TRUE; }


  ############################################################################################################################################

  ### Poll mem for load memory utilisation stats on UNIX-like hosts running UCD/Net-SNMPd
  #UCD-SNMP-MIB::memIndex.0 = INTEGER: 0
  #UCD-SNMP-MIB::memErrorName.0 = STRING: swap
  #UCD-SNMP-MIB::memTotalSwap.0 = INTEGER: 32762248 kB
  #UCD-SNMP-MIB::memAvailSwap.0 = INTEGER: 32199396 kB
  #UCD-SNMP-MIB::memTotalReal.0 = INTEGER: 8187696 kB
  #UCD-SNMP-MIB::memAvailReal.0 = INTEGER: 1211056 kB
  #UCD-SNMP-MIB::memTotalFree.0 = INTEGER: 33410452 kB
  #UCD-SNMP-MIB::memMinimumSwap.0 = INTEGER: 16000 kB
  #UCD-SNMP-MIB::memBuffer.0 = INTEGER: 104388 kB
  #UCD-SNMP-MIB::memCached.0 = INTEGER: 2595556 kB
  #UCD-SNMP-MIB::memSwapError.0 = INTEGER: noError(0)
  #UCD-SNMP-MIB::memSwapErrorMsg.0 = STRING:

  $mem_rrd_create = " --step 300 \
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
       RRA:MAX:0.5:288:800";

  $mem_cmd  = $config['snmpget'] . " -M ".$config['mibdir']. " -m UCD-SNMP-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
  $mem_cmd .= " memTotalSwap.0 memAvailSwap.0 memTotalReal.0 memAvailReal.0 memTotalFree.0 memShared.0 memBuffer.0 memCached.0";

  $mem_raw = shell_exec($mem_cmd);
  list($memTotalSwap, $memAvailSwap, $memTotalReal, $memAvailReal, $memTotalFree, $memShared, $memBuffer, $memCached) = explode("\n", str_replace(" kB", "", $mem_raw)); 

  ## Check to see that the OIDs are actually populated before we make the rrd
  if(is_numeric($memTotalReal) && is_numeric($memAvailReal) && is_numeric($memTotalFree))
  {
    if(!is_file($mem_rrd)) 
    {
      ## Create the rrd file if it doesn't exist
      rrdtool_create($mem_rrd, $mem_rrd_create);
    }
    rrdtool_update($mem_rrd,  "N:$memTotalSwap:$memAvailSwap:$memTotalReal:$memAvailReal:$memTotalFree:".($memShared+0).":".($memBuffer+0).":".($memCached+0));
    $graphs['ucd_memory'] = TRUE;
  }

  ##########################################################################################################################################################

  ### Poll laLoadInt for load averages on UNIX-like hosts running UCD/Net-SNMPd
  #UCD-SNMP-MIB::laLoadInt.1 = INTEGER: 206
  #UCD-SNMP-MIB::laLoadInt.2 = INTEGER: 429
  #UCD-SNMP-MIB::laLoadInt.3 = INTEGER: 479

  $la_load_create = " --step 300 \
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
  RRA:MAX:0.5:288:800";

  $load_get = "laLoadInt.1 laLoadInt.2 laLoadInt.3";
  $load_cmd = $config['snmpget']. " -M ".$config['mibdir']." -m UCD-SNMP-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " " . $load_get;
  $load_raw = `$load_cmd`;
  list ($load1, $load5, $load10) = explode ("\n", $load_raw);

  ## Check to see that the OIDs are actually populated before we make the rrd
  if(is_numeric($load1) && is_numeric($load5) && is_numeric($load10))
  {
    if(!is_file($load_rrd)) {
      rrdtool_create($load_rrd, $la_load_create);
    }
    rrdtool_update($load_rrd, "N:$load1:$load5:$load10");
    $graphs['ucd_load'] = "TRUE";
  }
