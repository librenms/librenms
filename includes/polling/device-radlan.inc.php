<?php


if($sysDescr == "Neyland 24T") {
  #$hardware = snmp_get($device, "productIdentificationVendor.0", "-Ovq", "Dell-Vendor-MIB");
  $hardware = "Dell ".snmp_get($device, "productIdentificationDisplayName.0", "-Ovq", "Dell-Vendor-MIB");
  $version  = snmp_get($device, "productIdentificationVersion.0", "-Ovq", "Dell-Vendor-MIB");
} else {
  $version  = snmp_get($device, "rndBrgVersion.0", "-Ovq", "RADLAN-MIB", $config['mib_dir'].":".$config['mib_dir'] . "radlan/");
  $hardware = str_replace("ATI", "Allied Telesis", $sysDescr);
}
$features = snmp_get($device, "rndBaseBootVersion.00", "-Ovq", "RADLAN-MIB", $config['mib_dir'].":".$config['mib_dir'] . "radlan/");

$version = str_replace("\"","", $version);
$features = str_replace("\"","", $features);
$hardware = str_replace("\"","", $hardware);




$cpurrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/radlan-cpu.rrd";
$cpu_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . " -m RADLAN-rndMng -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
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
