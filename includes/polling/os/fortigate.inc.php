<?php

$fnSysVersion = snmp_get($device, "FORTINET-FORTIGATE-MIB::fgSysVersion.0", "-Ovq");
$serial       = snmp_get($device, "FORTINET-FORTIGATE-MIB::fnSysSerial.0", "-Ovq");

$version = preg_replace("/(.+),(.+),(.+)/", "\\1||\\2||\\3", $fnSysVersion);
list($version,$features) = explode("||", $version);

if (isset($rewrite_fortinet_hardware[$poll_device['sysObjectID']]))
{
  $hardware = $rewrite_fortinet_hardware[$poll_device['sysObjectID']];
}

#$cmd  = $config['snmpget'] . " -M ".$config['mibdir']. " -m FORTINET-MIB-280 -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
#$cmd .= " fnSysCpuUsage.0 fnSysMemUsage.0 fnSysSesCount.0 fnSysMemCapacity.0";
#$data = shell_exec($cmd);
#list ($cpu, $mem, $ses, $memsize) = explode("\n", $data);

$sessrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/fortigate_sessions.rrd";
$sessions = snmp_get($device, "FORTINET-FORTIGATE-MIB::fgSysSesCount.0", "-Ovq");

if (is_numeric($sessions))
{
  if (!is_file($sessrrd))
  {
    rrdtool_create($sessrrd," --step 300 DS:sessions:GAUGE:600:0:3000000 ".$config['rrd_rra']);
  }
  print "Sessions: $sessions\n";
  rrdtool_update($sessrrd,"N:".$sessions);
  $graphs['fortigate_sessions'] = TRUE;
}

$cpurrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/fortigate_cpu.rrd";
$cpu_usage=snmp_get($device, "FORTINET-FORTIGATE-MIB::fgSysCpuUsage.0", "-Ovq");

if (is_numeric($cpu_usage))
{
  if (!is_file($cpurrd))
  {
    rrdtool_create($cpurrd," --step 300 DS:LOAD:GAUGE:600:-1:100 ".$config['rrd_rra']);
  }
  echo("CPU: $cpu_usage%\n");
  rrdtool_update($cpurrd, " N:$cpu_usage");
  $graphs['fortigate_cpu'] = TRUE;
}

#$mem=snmp_get($device, "FORTINET-FORTIGATE-MIB::fgSysMemUsage.0", "-Ovq");
#$memsize=snmp_get($device, "FORTINET-FORTIGATE-MIB::fgSysMemCapacity", "-Ovq");

?>
