<?php

if (strpos($poll_device['sysDescr'], "olive"))
{
  $hardware = "Olive";
  $serial = "";
}
else
{
  $junose_hardware  = snmp_get($device, "sysObjectID.0", "-Ovqs", "+Juniper-Products-MIB", $config['install_dir']."/mibs/junose");
  $junose_version   = snmp_get($device, "juniSystemSwVersion.0", "-Ovqs", "+Juniper-System-MIB", $config['install_dir']."/mibs/junose");
  $junose_serial    = "";

  $hardware = "Juniper " . rewrite_junose_hardware($junose_hardware);
}

list($version) = explode(" ", $junose_version);
list(,$version) =  explode("(", $version);
list($features) = explode("]", $junose_version);
list(,$features) =  explode("[", $features);

$cpurrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/junose-cpu.rrd";

#$cpu_cmd  = $config['snmpget'] . " -m JUNIPER-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
#$cpu_cmd .= " .1.3.6.1.4.1.2636.3.1.13.1.8.9.1.0.0";
#$cpu_usage = trim(shell_exec($cpu_cmd));

?>