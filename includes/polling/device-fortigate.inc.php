<?php

echo("Fortinet Fortigate Poller\n");

$fnSysVersion = shell_exec($config['snmpget']." -".$device['snmpver']." -Ovq -c ".$device['community']." ".$device['hostname'].":".$device['port']." fnSysVersion.0");
$serial       = shell_exec($config['snmpget']." -".$device['snmpver']." -Ovq -c ".$device['community']." ".$device['hostname'].":".$device['port']." fnSysSerial.0");

$version = preg_replace("/(.+)\ (.+),(.+),(.+)/", "Fortinet \\1||\\2||\\3||\\4", $fnSysVersion);
list($hardware,$version,$features) = explode("||", $version);

$cpurrd   = $rrd_dir . "/" . $device['hostname'] . "/fortigate-cpu.rrd";
$memrrd   = $rrd_dir . "/" . $device['hostname'] . "/fortigate-memory.rrd";
$sessrrd  = $rrd_dir . "/" . $device['hostname'] . "/fortigate-sessions.rrd";

$cmd  = $config['snmpget'] . " -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
$cmd .= " fnSysCpuUsage.0 fnSysMemUsage.0 fnSysSesCount.0 fnSysMemCapacity.0";
$data = shell_exec($cmd);
list ($cpu, $mem, $ses, $memsize) = explode("\n", $data);

if (!is_file($cpurrd)) {
   shell_exec($config['rrdtool']." create $cpurrd --step 300 DS:cpu:GAUGE:600:0:100 \
     RRA:AVERAGE:0.5:1:800 RRA:AVERAGE:0.5:6:800 RRA:AVERAGE:0.5:24:800 RRA:AVERAGE:0.5:288:800 \
     RRA:MAX:0.5:1:800 RRA:MAX:0.5:6:800 RRA:MAX:0.5:24:800 RRA:MAX:0.5:288:800");
}

if (!is_file($memrrd)) {

      shell_exec($config['rrdtool'] . " create $memrrd --step 300 \
       DS:mem:GAUGE:600:0:10000000000 DS:memcapacity:GAUGE:600:0:10000000000 \
       RRA:AVERAGE:0.5:1:800 RRA:AVERAGE:0.5:6:800 RRA:AVERAGE:0.5:24:800 RRA:AVERAGE:0.5:288:800 \
       RRA:MAX:0.5:1:800 RRA:MAX:0.5:6:800 RRA:MAX:0.5:24:800 RRA:MAX:0.5:288:800");
}

if (!is_file($sessrrd)) {
   `rrdtool create $sessrrd --step 300 DS:sessions:GAUGE:600:0:3000000 \
     RRA:AVERAGE:0.5:1:800 RRA:AVERAGE:0.5:6:800 RRA:AVERAGE:0.5:24:800 RRA:AVERAGE:0.5:288:800 \
     RRA:MAX:0.5:1:800 RRA:MAX:0.5:6:800 RRA:MAX:0.5:24:800 RRA:MAX:0.5:288:800`;
}

shell_exec($config['rrdtool'] . " update $cpurrd N:$cpu");
shell_exec($config['rrdtool'] . " update $memrrd N:$mem:$memsize");
shell_exec($config['rrdtool'] . " update $sessrrd N:$ses");

?>
