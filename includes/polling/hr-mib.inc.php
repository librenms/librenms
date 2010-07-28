<?php

/// HOST-RESOURCES-MIB
//  Generic System Statistics

$hrSystem_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/hrSystem.rrd";

$oid_list = "hrSystemProcesses.0 hrSystemNumUsers.0";
$hrSystem  = snmp_get_multi ($device, $oid_list, "-OUQs", "HOST-RESOURCES-MIB");

if(isset($hrSystem[0]['hrSystemProcesses'])) 
{
  $rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/hr-processes.rrd";
  if (!is_file($rrd_file)) {
    shell_exec($config['rrdtool'] . " create $rrd_file \
    --step 300 \
    DS:procs:GAUGE:600:0:U \
    RRA:AVERAGE:0.5:1:800 \
    RRA:AVERAGE:0.5:6:800 \
    RRA:AVERAGE:0.5:24:800 \
    RRA:AVERAGE:0.5:288:800 \
    RRA:MAX:0.5:1:800 \
    RRA:MAX:0.5:6:800 \
    RRA:MAX:0.5:24:800 \
    RRA:MAX:0.5:288:800");
  }
  rrdtool_update($rrd_file,  "N:$hrSystemProcesses");
  $graphs['hrprocesses'] = TRUE;
}

if(isset($hrSystem[0]['hrSystemNumUsers'])) 
{
  $rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/hr-users.rrd";
  if (!is_file($rrd_file)) {
    shell_exec($config['rrdtool'] . " create $rrd_file \
    --step 300 \
    DS:users:GAUGE:600:0:U \
    RRA:AVERAGE:0.5:1:800 \
    RRA:AVERAGE:0.5:6:800 \
    RRA:AVERAGE:0.5:24:800 \
    RRA:AVERAGE:0.5:288:800 \
    RRA:MAX:0.5:1:800 \
    RRA:MAX:0.5:6:800 \
    RRA:MAX:0.5:24:800 \
    RRA:MAX:0.5:288:800");
  }
  rrdtool_update($rrd_file,  "N:$hrSystemNumUsers");
  $graphs['hrusers'] = TRUE;
}


?>
