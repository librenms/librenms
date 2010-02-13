<?php

   $community = $device['community'];
   $id = $device['device_id'];
   $hostname = $device['hostname'];
   $port = $device['port'];
   $snmpver = $device['snmpver'];

   $cpurrd   = $config['rrd_dir'] . "/" . $hostname . "/ios-cpu.rrd";
   $memrrd   = $config['rrd_dir'] . "/" . $hostname . "/ios-mem.rrd";

   $sysDescr = str_replace("IOS (tm)", "IOS (tm),", $sysDescr);
   list(,$features,$version) = explode(",", $sysDescr);
   $version = str_replace(" Version ", "", $version);
   list(,$features) = explode("(", $features);
   list(,$features) = explode("-", $features);

   $snmp_cmdb =  $config['snmpget'] . " -m ENTITY-MIB:OLD-CISCO-CHASSIS-MIB -O Qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .
                 $device['hostname'].":".$device['port'];
   $snmp_cmdb .= " entPhysicalModelName.1 entPhysicalContainedIn.1 entPhysicalName.1 entPhysicalSoftwareRev.1 ";
   $snmp_cmdb .= " entPhysicalModelName.1001 entPhysicalContainedIn.1001";
   $snmp_cmdb .= " cardDescr.1 cardSlotNumber.1";
   list($model_1,$contained_1,$name_1,$ver_1,$model_1001,$contained_1001,$descr_1,$slot_1) = explode("\n", shell_exec($snmp_cmdb));
   if(($contained_1 == "0" || $name_1 == "Chassis") && strpos($model_1, "No") === FALSE) { $ciscomodel = $model_1; list($version_1) = explode(",",$ver_1); }
   if($slot_1 == "-1" && strpos($descr_1, "No") === FALSE) { $ciscomodel = $descr_1; }
   if($contained_1001 == "0" && strpos($model_1001, "No") === FALSE) { $ciscomodel = $model_1001; }
   $ciscomodel = str_replace("\"","",$ciscomodel);
   if($ciscomodel) { $hardware = $ciscomodel; unset($ciscomodel); }


   $cpu5m = shell_exec($config['snmpget'] . " -m OLD-CISCO-CPU-MIB -O qv -$snmpver -c $community $hostname:$port avgBusy5.0");
   $cpu5m = $cpu5m + 0;

   echo("$hostname\n");

   if (!is_file($cpurrd)) {
      $rrdcreate = shell_exec($config['rrdtool'] . " create $cpurrd --step 300 \
                    DS:LOAD5M:GAUGE:600:-1:100 \
                    RRA:AVERAGE:0.5:1:2000 \
                    RRA:AVERAGE:0.5:6:2000 \
                    RRA:AVERAGE:0.5:24:2000 \
                    RRA:AVERAGE:0.5:288:2000 \
                    RRA:MAX:0.5:1:2000 \
                    RRA:MAX:0.5:6:2000 \
                    RRA:MAX:0.5:24:2000 \
                    RRA:MAX:0.5:288:2000 \
                    RRA:MIN:0.5:1:2000 \
                    RRA:MIN:0.5:6:2000 \
                    RRA:MIN:0.5:24:2000 \
                    RRA:MIN:0.5:288:2000");
   }

   shell_exec($config['rrdtool'] . " update $cpurrd N:$cpu5m");

   $mem_get  = ".1.3.6.1.4.1.9.9.48.1.1.1.6.2 .1.3.6.1.4.1.9.9.48.1.1.1.6.1 .1.3.6.1.4.1.9.9.48.1.1.1.6.3";
   $mem_get .= ".1.3.6.1.4.1.9.9.48.1.1.1.5.2 .1.3.6.1.4.1.9.9.48.1.1.1.5.1 .1.3.6.1.4.1.9.9.48.1.1.1.5.3";
   $mem_raw  = shell_exec($config['snmpget'] . " -O qv -".$device['snmpver']." -c $community $hostname:$port $mem_get");
   $mem_raw  = str_replace("No Such Instance currently exists at this OID", "0", $mem_raw); 
   list ($memfreeio, $memfreeproc, $memfreeprocb, $memusedio, $memusedproc, $memusedprocb) = explode("\n", $mem_raw); 
   $memfreeproc = $memfreeproc + $memfreeprocb;
   $memusedproc = $memusedproc + $memusedprocb;
   $memfreeio = $memfreeio + 0;
   $memfreeproc = $memfreeproc + 0;
   $memusedio = $memusedio + 0;
   $memusedproc = $memusedproc + 0;
   $memtotal = $memfreeio + $memfreeproc + $memusedio + $memusedproc;
   if (!is_file($memrrd)) {
      shell_exec($config['rrdtool'] ." create $memrrd --step 300 \
                    DS:IOFREE:GAUGE:600:0:U \
                    DS:IOUSED:GAUGE:600:-1:U \
                    DS:PROCFREE:GAUGE:600:0:U \
                    DS:PROCUSED:GAUGE:600:-1:U \
                    DS:MEMTOTAL:GAUGE:600:-1:U \
                    RRA:AVERAGE:0.5:1:2000 \
                    RRA:AVERAGE:0.5:6:2000 \
                    RRA:AVERAGE:0.5:24:2000 \
                    RRA:AVERAGE:0.5:288:2000 \
                    RRA:MAX:0.5:1:2000 \
                    RRA:MAX:0.5:6:2000 \
                    RRA:MAX:0.5:24:2000 \
                    RRA:MAX:0.5:288:2000 \
                    RRA:MIN:0.5:1:2000 \
                    RRA:MIN:0.5:6:2000 \
                    RRA:MIN:0.5:24:2000 \
                    RRA:MIN:0.5:288:2000");

   }
   rrdtool_update ($memrrd, "N:$memfreeio:$memusedio:$memfreeproc:$memusedproc:$memtotal");

#   include("includes/polling/cisco-processors.inc.php");
   include("includes/polling/cisco-enhanced-mempool.inc.php");
   include("includes/polling/cisco-mempool.inc.php");
   include("includes/polling/cisco-entity-sensors.inc.php");

?>
