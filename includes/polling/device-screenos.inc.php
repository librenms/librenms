<?php

echo("Doing Juniper Netscreen (ScreenOS)");

$version = preg_replace("/(.+)\ version\ (.+)\ \(SN:\ (.+)\,\ (.+)\)/", "Juniper Netscreen \\1||\\2||\\3||\\4", $sysDescr);
echo("$version\n");
list($hardware,$version,$serial,$features) = explode("||", $version);

$cpurrd   = $rrd_dir . "/" . $device['hostname'] . "/netscreen-cpu.rrd";
$memrrd   = $rrd_dir . "/" . $device['hostname'] . "/netscreen-memory.rrd";
$sessrrd  = $rrd_dir . "/" . $device['hostname'] . "/netscreen-sessions.rrd";

$cpu_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . " -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
$cpu_cmd .= " .1.3.6.1.4.1.3224.16.1.1.0 .1.3.6.1.4.1.3224.16.1.3.0";
$cpu_data = shell_exec($cpu_cmd);
list ($cpuav, $cpu5m) = explode("\n", $cpu_data);

$mem_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . " -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
$mem_cmd .= " .1.3.6.1.4.1.3224.16.2.1.0 .1.3.6.1.4.1.3224.16.2.2.0 .1.3.6.1.4.1.3224.16.2.3.0";
$mem_data = shell_exec($mem_cmd);
list ($memalloc, $memfree, $memfrag) = explode("\n", $mem_data);


$sess_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . " -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
$sess_cmd .= " .1.3.6.1.4.1.3224.16.3.2.0 .1.3.6.1.4.1.3224.16.3.3.0 .1.3.6.1.4.1.3224.16.3.4.0";
$sess_data = shell_exec($sess_cmd);
list ($sessalloc, $sessmax, $sessfailed) = explode("\n", $sess_data);

if (!is_file($cpurrd)) {
   `rrdtool create $cpurrd \
    --step 300 \
     DS:average:GAUGE:600:0:100 \
     DS:5min:GAUGE:600:0:100 \
     RRA:AVERAGE:0.5:1:800 \
     RRA:AVERAGE:0.5:6:800 \
     RRA:AVERAGE:0.5:24:800 \
     RRA:AVERAGE:0.5:288:800 \
     RRA:MAX:0.5:1:800 \
     RRA:MAX:0.5:6:800 \
     RRA:MAX:0.5:24:800 \
     RRA:MAX:0.5:288:800`;
}
if (!is_file($memrrd)) {

      shell_exec("rrdtool create $memrrd \
       --step 300 \
       DS:allocate:GAUGE:600:0:10000000000 \
       DS:left:GAUGE:600:0:10000000000 \
       DS:frag:GAUGE:600:0:10000000000 \
       RRA:AVERAGE:0.5:1:800 \
       RRA:AVERAGE:0.5:6:800 \
       RRA:AVERAGE:0.5:24:800 \
       RRA:AVERAGE:0.5:288:800 \
       RRA:MAX:0.5:1:800 \
       RRA:MAX:0.5:6:800 \
       RRA:MAX:0.5:24:800 \
       RRA:MAX:0.5:288:800");
}

if (!is_file($sessrrd)) {
   `rrdtool create $sessrrd \
    --step 300 \
     DS:allocate:GAUGE:600:0:3000000 \
     DS:max:GAUGE:600:0:3000000 \
     DS:failed:GAUGE:600:0:1000 \
     RRA:AVERAGE:0.5:1:800 \
     RRA:AVERAGE:0.5:6:800 \
     RRA:AVERAGE:0.5:24:800 \
     RRA:AVERAGE:0.5:288:800 \
     RRA:MAX:0.5:1:800 \
     RRA:MAX:0.5:6:800 \
     RRA:MAX:0.5:24:800 \
     RRA:MAX:0.5:288:800`;
}


shell_exec($config['rrdtool'] . " update $cpurrd N:$cpuav:$cpu5m");
shell_exec($config['rrdtool'] . " update $memrrd N:$memalloc:$memfree:$memfrag");
shell_exec($config['rrdtool'] . " update $sessrrd N:$sessalloc:$sessmax:$sessfailed");

?>
