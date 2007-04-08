<?

include("procurve-graphing.php");

function pollDeviceHP() {

   global $device;
   global $community;
   $id = $device['device_id'];
   $hostname = $device['hostname'];
   $hardware = $device['hardware'];
   $version = $device['version'];
   $features = $device['features'];
   $location = $device['location'];
   $os = $device['location'];
   $cpurrd   = "rrd/" . $hostname . "-cpu.rrd";
   $memrrd   = "rrd/" . $hostname . "-mem.rrd";
   $cpu = `snmpget -O qv -v2c -c $community $hostname 1.3.6.1.4.1.11.2.14.11.5.1.9.6.1.0`;
   $meminfo = `snmpget -O qv -v2c -c $community $hostname 1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.5.1 1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.6.1 1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.7.1`;
   echo("$meminfo");
   list ($memtotal, $memfree, $memused) = explode("\n", $meminfo);
   echo("$hostname\n");
   $memused = $memused + 0;
   $memfree = $memfree + 0;
   $memtotal = $memtotal + 0;
   if (!is_file($cpurrd)) {
      $rrdcreate = `rrdtool create $cpurrd --step 300 DS:LOAD:GAUGE:600:-1:100 RRA:AVERAGE:0.5:1:1200`;
   }
   if (!is_file($memrrd)) {
      $rrdcreate = `rrdtool create $memrrd --step 300 DS:TOTAL:GAUGE:600:0:500000000 DS:FREE:GAUGE:600:-1:500000000 DS:USED:GAUGE:600:0:500000000 RRA:AVERAGE:0.5:1:1200`;
   }
   `rrdtool update $cpurrd N:$cpu`;
   `rrdtool update $memrrd N:$memtotal:$memfree:$memused`;
}

?>
