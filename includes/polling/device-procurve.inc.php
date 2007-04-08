<?

$cpurrd   = "rrd/" . $device['hostname'] . "-cpu.rrd";
$memrrd   = "rrd/" . $device['hostname'] . "-mem.rrd";

$cpu_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " 1.3.6.1.4.1.11.2.14.11.5.1.9.6.1.0`;
$cpu     = `$cpu_cmd`;


$mem_cmd  = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
$mem_cmd .= " 1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.5.1 1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.6.1 1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.7.1`;
$mem      = `$mem_cmd`;

list ($memtotal, $memfree, $memused) = explode("\n", $mem);

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

?>
