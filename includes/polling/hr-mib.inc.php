<?php

// HOST-RESOURCES-MIB
//  Generic System Statistics

$oid_list = "hrSystemProcesses.0 hrSystemNumUsers.0";
$hrSystem  = snmp_get_multi ($device, $oid_list, "-OUQs", "HOST-RESOURCES-MIB");

echo("HR Stats:");

if (is_numeric($hrSystem[0]['hrSystemProcesses']))
{
  $rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/hr_processes.rrd";
  if (!is_file($rrd_file))
  {
    rrdtool_create($rrd_file,"--step 300 \
    DS:procs:GAUGE:600:0:U ".$config['rrd_rra']);
  }

  rrdtool_update($rrd_file,  "N:".$hrSystem[0]['hrSystemProcesses']);
  $graphs['hr_processes'] = TRUE;
  echo(" Processes");
}

if (is_numeric($hrSystem[0]['hrSystemNumUsers']))
{
  $rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/hr_users.rrd";
  if (!is_file($rrd_file))
  {
    rrdtool_create($rrd_file, "--step 300 \
    DS:users:GAUGE:600:0:U ".$config['rrd_rra']);
  }

  rrdtool_update($rrd_file,  "N:".$hrSystem[0]['hrSystemNumUsers']);
  $graphs['hr_users'] = TRUE;
  echo(" Users");
}

echo("\n");

?>
