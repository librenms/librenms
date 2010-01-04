<?php


$snmp_cmd =  $config['snmpget'] . " -m Dell-Vendor-MIB -O Qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .
                 $device['hostname'].":".$device['port'];
$snmp_cmd .= " productIdentificationDisplayName.0 productIdentificationVersion.0 productIdentificationDescription.0";

list($hardware, $version, $features) = explode("\n", shell_exec($snmp_cmd));

$cpurrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/powerconnect-cpu.rrd";

$cpu_cmd  = $config['snmpget'] . " -m RADLAN-rndMng -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
$cpu_cmd .= " RADLAN-rndMng::rlCpuUtilDuringLastSecond.0";
$cpu_usage = trim(shell_exec($cpu_cmd));

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

rrdtool_update($cpurrd, "N:$cpu_usage");

?>
