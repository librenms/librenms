<?php

list($hardware, $features, $version) = explode(",", str_replace(", ", ",", $sysDescr));
list($version) = explode("(", $version);



$cpurrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/procurve-cpu.rrd";
$memrrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/procurve-mem.rrd";

$cpu_cmd = $config['snmpget'] . " -M ".$config['mibdir'] . " -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " 1.3.6.1.4.1.11.2.14.11.5.1.9.6.1.0";
$cpu     = shell_exec($cpu_cmd);

$mem_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . " -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
$mem_cmd .= " 1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.5.1 1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.6.1 1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.7.1";
$mem      = shell_exec($mem_cmd);

list ($memtotal, $memfree, $memused) = explode("\n", $mem);

$memused = $memused + 0;
$memfree = $memfree + 0;
$memtotal = $memtotal + 0;

if (!is_file($cpurrd)) {
   $rrdcreate = shell_exec($config['rrdtool'] ." create $cpurrd --step 300 DS:LOAD:GAUGE:600:-1:100 RRA:AVERAGE:0.5:1:1200                  RRA:AVERAGE:0.5:1:2000 \
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
if (!is_file($memrrd)) {
   $rrdcreate = shell_exec($config['rrdtool'] ." create $memrrd --step 300 DS:TOTAL:GAUGE:600:0:500000000 DS:FREE:GAUGE:600:-1:500000000 DS:USED:GAUGE:600:0:500000000 RRA:AVERAGE:0.5:1:1200                  RRA:AVERAGE:0.5:1:2000 \
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

rrdtool_update($cpurrd, "N:$cpu");
rrdtool_update($memrrd, "N:$memtotal:$memfree:$memused");

?>
