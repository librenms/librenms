<?php

   $community = $device['community'];
   $id = $device['device_id'];
   $hostname = $device['hostname'];
   $port = $device['port'];
   $snmpver = $device['snmpver'];

   $temprrd  = $config['rrd_dir'] . "/" . $hostname . "/temp.rrd";
   $cpurrd   = $config['rrd_dir'] . "/" . $hostname . "/cpu.rrd";
   $memrrd   = $config['rrd_dir'] . "/" . $hostname . "/mem.rrd";

   if(strstr($ciscomodel, "OID")){ unset($ciscomodel); }
   if(!strstr($ciscomodel, " ") && strlen($ciscomodel) >= '3') {
     $hardware = $ciscomodel;
   }

   $sysDescr = str_replace("IOS (tm)", "IOS (tm),", $sysDescr);
   list(,$features,$version) = explode(",", $sysDescr);
   $version = str_replace("Copyright", "", $version);
   list(,$version,) = explode(" ", trim($version));
   list(,$features) = explode("(", $features);
   list(,$features) = explode("-", $features);




   list ($cpu5m, $cpu5s) = explode("\n", shell_exec($config['snmpget'] . " -m OLD-CISCO-CPU-MIB -O qv -$snmpver -c $community $hostname:$port 1.3.6.1.4.1.9.2.1.58.0 1.3.6.1.4.1.9.2.1.56.0"));
   $cpu5m = $cpu5m + 0;
   $cpu5s = $cpu5s + 0;

   echo("$hostname\n");

   if (!is_file($cpurrd)) {
      $rrdcreate = `rrdtool create $cpurrd --step 300 \
                    DS:LOAD5S:GAUGE:600:-1:100 \
                    DS:LOAD5M:GAUGE:600:-1:100 \
                    RRA:AVERAGE:0.5:1:2000 \
                    RRA:AVERAGE:0.5:6:2000 \
                    RRA:AVERAGE:0.5:24:2000 \
                    RRA:AVERAGE:0.5:288:2000 \
                    RRA:MAX:0.5:1:2000 \
                    RRA:MAX:0.5:6:2000 \
                    RRA:MAX:0.5:24:2000 \
                    RRA:MAX:0.5:288:2000`;
   }

   `rrdtool update $cpurrd N:$cpu5s:$cpu5m`;

   include("includes/polling/cisco-processors.inc.php");
   include("includes/polling/cisco-mempool.inc.php");
   include("includes/polling/cisco-entity-sensors.inc.php");

?>
