<?php

## FIXME - find some fortigate hardware to test this on, and to update and generify it

$fnSysVersion = snmp_get($device, "FORTINET-MIB-280::fnSysVersion.0", "-Ovq");
$serial       = snmp_get($device, "FORTINET-MIB-280::fnSysSerial.0", "-Ovq");

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

$sessions = snmp_get($device, "fnSysSesCount.0", "FORTINET-MIB-280");

if (is_numeric($sessions))
{
  if (!is_file($sessrrd))
  {
    rrdtool_create($sessrrd," --step 300 DS:sessions:GAUGE:600:0:3000000 \
     RRA:AVERAGE:0.5:1:800 RRA:AVERAGE:0.5:6:800 RRA:AVERAGE:0.5:24:800 RRA:AVERAGE:0.5:288:800 \
     RRA:MAX:0.5:1:800 RRA:MAX:0.5:6:800 RRA:MAX:0.5:24:800 RRA:MAX:0.5:288:800");
  }
  rrdtool_update($sessrrd,"N:".$ses);
  $graphs['fortigate_sessions'] = TRUE;
}

?>
