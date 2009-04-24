<?php

echo("Doing Juniper JunOS");

$jun_ver =  trim(str_replace("\"", "", shell_exec($config['snmpget'] . " -m HOST-RESOURCES-MIB -".$device['snmpver']." -Oqv -c ".$device['community']." ".$device['hostname']." .1.3.6.1.2.1.25.6.3.1.2.2")));
$hardware = trim(str_replace("\"", "", shell_exec($config['snmpget'] . " -m JUNIPER-MIB -".$device['snmpver']." -Oqv -c ".$device['community']." ".$device['hostname']." .1.3.6.1.4.1.2636.3.1.2.0")));
$serial = trim(str_replace("\"", "", shell_exec($config['snmpget'] . " -m JUNIPER-MIB -".$device['snmpver']." -Oqv -c ".$device['community']." ".$device['hostname']." .1.3.6.1.4.1.2636.3.1.3.0")));

$version = preg_replace("/.+\[(.+)\].+/", "\\1", $jun_ver);
$features = preg_replace("/.+\ \((.+)\)$/", "\\1", $jun_ver);

echo("$hardware - $version - $features - $serial\n");

$cpurrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/junos-cpu.rrd";

$cpu_cmd  = $config['snmpget'] . " -m JUNIPER-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
$cpu_cmd .= " .1.3.6.1.4.1.2636.3.1.13.1.8.9.1.0.0";
$cpu_usage = trim(shell_exec($cpu_cmd));

if (!is_file($cpurrd)) {
   `rrdtool create $cpurrd \
    --step 300 \
     DS:cpu:GAUGE:600:0:100 \
     RRA:AVERAGE:0.5:1:800 \
     RRA:AVERAGE:0.5:6:800 \
     RRA:AVERAGE:0.5:24:800 \
     RRA:AVERAGE:0.5:288:800 \
     RRA:MAX:0.5:1:800 \
     RRA:MAX:0.5:6:800 \
     RRA:MAX:0.5:24:800 \
     RRA:MAX:0.5:288:800`;
}

shell_exec($config['rrdtool'] . " update $cpurrd N:$cpu_usage");


?>
