<?

   $community = $device['community'];
   $id = $device['device_id'];
   $hostname = $device['hostname'];
   $port = $device['port'];

   $temprrd  = $config['rrd_dir'] . "/" . $hostname . "/temp.rrd";
   $cpurrd   = $config['rrd_dir'] . "/" . $hostname . "/cpu.rrd";
   $memrrd   = $config['rrd_dir'] . "/" . $hostname . "/mem.rrd";

   $version = str_replace("Cisco IOS Software,", "", $sysDescr);
   $version = str_replace("IOS (tm) ", "", $version);
   $version = str_replace(",RELEASE SOFTWARE", "", $version);
   $version = str_replace(",MAINTENANCE INTERIM SOFTWARE", "", $version);
   $version = str_replace("Version ","", $version);
   $version = str_replace("Cisco Internetwork Operating System Software", "", $version);
   $version = trim($version);
   list($version) = explode("\n", $version);
   $version = preg_replace("/^[A-Za-z0-9\ \_]*\(([A-Za-z0-9\-\_]*)\), (.+), .*/", "\\1|\\2", $version);
   $version = str_replace("-M|", "|", $version);
   $version = str_replace("-", "|", $version);
   list($hardware, $features, $version) = explode("|", $version);
   #$features = rewrite_ios_features($features);
   #$hardware = fixIOSHardware($hardware);
   if(strstr($ciscomodel, "OID")){ unset($ciscomodel); }
   if(!strstr($ciscomodel, " ") && strlen($ciscomodel) >= '3') {
     $hardware = $ciscomodel;
   }

   if($device['os'] == "IOS XE") {
     list(,$features,$version) = explode(",", $sysDescr);
     $version = str_replace(" Version ", "", $version);
     $features = str_replace(" IOS-XE Software (", "", $features);
     $features = str_replace("-M", "", $features);
     $features = str_replace(")", "", $features);
     $features = str_replace("PPC_LINUX_IOSD-", "", $features);
     #$features = rewrite_ios_features($features);
   }


   list ($cpu5m, $cpu5s) = explode("\n", `snmpget -O qv -v2c -c $community $hostname:$port 1.3.6.1.4.1.9.2.1.58.0 1.3.6.1.4.1.9.2.1.56.0`);
   $cpu5m = $cpu5m + 0;
   $cpu5s = $cpu5s + 0;
   list ($tempin1, $tempout1) = explode("\n", `snmpget -O qv -v2c -c $community $hostname:$port .1.3.6.1.4.1.9.9.13.1.3.1.3.1 .1.3.6.1.4.1.9.9.13.1.3.1.3.2`);
   $tempin1 = $tempin1 +0;
   $tempout1 = $tempout1 + 0;
   $mem_get  = ".1.3.6.1.4.1.9.9.48.1.1.1.6.2 .1.3.6.1.4.1.9.9.48.1.1.1.6.1 .1.3.6.1.4.1.9.9.48.1.1.1.6.3";
   $mem_get .= ".1.3.6.1.4.1.9.9.48.1.1.1.5.2 .1.3.6.1.4.1.9.9.48.1.1.1.5.1 .1.3.6.1.4.1.9.9.48.1.1.1.5.3";
   $mem_raw  = `snmpget -O qv -v2c -c $community $hostname:$port $mem_get`;
   $mem_raw  = str_replace("No Such Instance currently exists at this OID", "0", $mem_raw); 
   list ($memfreeio, $memfreeproc, $memfreeprocb, $memusedio, $memusedproc, $memusedprocb) = explode("\n", $mem_raw); 
   echo("$hostname\n");
   $memfreeproc = $memfreeproc + $memfreeprocb;
   $memusedproc = $memusedproc + $memusedprocb;
   $memfreeio = $memfreeio + 0;
   $memfreeproc = $memfreeproc + 0;
   $memusedio = $memusedio + 0;
   $memusedproc = $memusedproc + 0;
   $memtotal = $memfreeio + $memfreeproc + $memusedio + $memusedproc;
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
   if (!is_file($temprrd)) {
      $rrdcreate = `rrdtool create $temprrd --step 300 \
                    DS:TEMPIN1:GAUGE:600:-25:100 \
                    DS:TEMPOUT1:GAUGE:600:-25:100 \
                    RRA:AVERAGE:0.5:1:2000 \
                    RRA:AVERAGE:0.5:6:2000 \
                    RRA:AVERAGE:0.5:24:2000 \
                    RRA:AVERAGE:0.5:288:2000 \
                    RRA:MAX:0.5:1:2000 \
                    RRA:MAX:0.5:6:2000 \
                    RRA:MAX:0.5:24:2000 \
                    RRA:MAX:0.5:288:2000`;
   }
   if (!is_file($memrrd)) {
      $rrdcreate = `rrdtool create $memrrd --step 300 \
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
                    RRA:MAX:0.5:288:2000`;

   }
   `rrdtool update $temprrd N:$tempin1:$tempout1`;
   `rrdtool update $cpurrd N:$cpu5s:$cpu5m`;
   `rrdtool update $memrrd N:$memfreeio:$memusedio:$memfreeproc:$memusedproc:$memtotal`;

   include("includes/polling/bgpPeer.inc.php");
   include("includes/polling/cisco-processors.inc.php");

?>
