<?

function pollDevice() {

   global $device;
   global $community;
   $id = $device['id'];
   $hostname = $device['hostname'];
   $hardware = $device['hardware'];
   $version = $device['version'];
   $features = $device['features'];
   $location = $device['location'];
   $os = $device['location'];

   $temprrd  = "rrd/" . $hostname . "-temp.rrd";
   $tempgraph = "public_html/graphs/" . $hostname . "-temp.png";
   $cpurrd   = "rrd/" . $hostname . "-cpu.rrd";
   $cpugraph = "public_html/graphs/" . $hostname . "-cpu.png";   
   $memrrd   = "rrd/" . $hostname . "-mem.rrd";
   $memgraph = "public_html/graphs/" . $hostname . "-mem.png";
   list ($cpu5m, $cpu5s) = explode("\n", `snmpget -O qv -v2c -c $community $hostname 1.3.6.1.4.1.9.2.1.58.0 1.3.6.1.4.1.9.2.1.56.0`);
   $cpu5m = $cpu5m + 0;
   $cpu5s = $cpu5s + 0;
   list ($tempin1, $tempout1) = explode("\n", `snmpget -O qv -v2c -c $community $hostname .1.3.6.1.4.1.9.9.13.1.3.1.3.1 .1.3.6.1.4.1.9.9.13.1.3.1.3.2`);
   $tempin1 = $tempin1 +0;
   $tempout1 = $tempout1 + 0;
   list ($memfreeio, $memfreeproc, $memusedio, $memusedproc) = explode("\n", `snmpget -O qv -v2c -c $community $hostname .1.3.6.1.4.1.9.9.48.1.1.1.6.2 .1.3.6.1.4.1.9.9.48.1.1.1.6.1 .1.3.6.1.4.1.9.9.48.1.1.1.5.2 .1.3.6.1.4.1.9.9.48.1.1.1.5.1`);
   echo("$hostname\n");
   $memfreeio = $memfreeio + 0;
   $memfreeproc = $memfreeproc + 0;
   $memusedio = $memusedio + 0;
   $memusedproc = $memusedproc + 0;
   $memtotal = $memfreeio + $memfreeproc + $memusedio + $memusedproc;
   if (!is_file($cpurrd)) {
      $rrdcreate = `rrdtool create $cpurrd --step 300 DS:LOAD5S:GAUGE:600:-1:100 DS:LOAD5M:GAUGE:600:-1:100 RRA:AVERAGE:0.5:1:1200`;
   }
   if (!is_file($temprrd)) {
      $rrdcreate = `rrdtool create $temprrd --step 300 DS:TEMPIN1:GAUGE:600:-1:100 DS:TEMPOUT1:GAUGE:600:-1:100 RRA:AVERAGE:0.5:1:1200`;
   }
   if (!is_file($memrrd)) {
      $rrdcreate = `rrdtool create $memrrd --step 300 DS:IOFREE:GAUGE:600:0:500000000 DS:IOUSED:GAUGE:600:-1:500000000 DS:PROCFREE:GAUGE:600:0:500000000 DS:PROCUSED:GAUGE:600:-1:500000000 DS:MEMTOTAL:GAUGE:600:-1:500000000 RRA:AVERAGE:0.5:1:1200`;
   }
   `rrdtool update $temprrd N:$tempin1:$tempout1`;
   `rrdtool update $cpurrd N:$cpu5s:$cpu5m`;
   `rrdtool update $memrrd N:$$memfreeio:$memusedio:$memfreeproc:$memusedproc:$memtotal`;
}

?>
