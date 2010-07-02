<?php

echo("Doing Juniper JunOS ");
## FIX ME below
$jun_ver  =  trim(str_replace("\"", "", shell_exec($config['snmpget'] . " -M ".$config['mibdir'] . " -m HOST-RESOURCES-MIB -".$device['snmpver']." -Oqv -c ".$device['community']." ".$device['hostname'].":".$device['port']." .1.3.6.1.2.1.25.6.3.1.2.2")));
if(strpos($sysDescr, "olive")) {
  $hardware = "Olive";
  $serial = "";
} else {
  $hardware = snmp_get($device, ".1.3.6.1.4.1.2636.3.1.2.0", "-OQv", "+JUNIPER-MIB", "+".$config['install_dir']."/mibs/junos");
  $serial   = snmp_get($device, ".1.3.6.1.4.1.2636.3.1.3.0", "-OQv", "+JUNIPER-MIB", "+".$config['install_dir']."/mibs/junos");
  list(,$hardware,) = explode(" ", $hardware);
  $hardware = "Juniper " . $hardware;
}

list($version) = explode("]", $jun_ver);
list(,$version) =  explode("[", $version);
$features = "";

echo("$hardware - $version - $features - $serial\n");

$cpurrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/junos-cpu.rrd";

#$cpu_cmd  = $config['snmpget'] . " -m JUNIPER-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
#$cpu_cmd .= " .1.3.6.1.4.1.2636.3.1.13.1.8.9.1.0.0";
#$cpu_usage = trim(shell_exec($cpu_cmd));

if (is_numeric($cpu_usage))
{
  if (!is_file($cpurrd)) 
  {
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
  echo "CPU: $cpu_usage%\n";
  rrdtool_update($cpurrd, " N:$cpu_usage");
}

include("hr-mib.inc.php");

?>
