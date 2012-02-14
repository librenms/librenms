<?php

list($hardware, $version, $features) = explode(",", str_replace(", ", ",", $poll_device['sysDescr']));
list($features) = explode("(", $version);

$fdb_rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/fdb_count.rrd";

$FdbAddressCount = snmp_get ($device, "hpSwitchFdbAddressCount.0", "-Ovqn", "STATISTICS-MIB");

if (is_numeric($FdbAddressCount))
{
  if (!is_file($fdb_rrd_file))
  {
    rrdtool_create($fdb_rrd_file, " --step 300 \
                    DS:value:GAUGE:600:-1:100000 \
                    RRA:AVERAGE:0.5:1:1200 \
                    RRA:AVERAGE:0.5:1:2000 \
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

  rrdtool_update($fdb_rrd_file, "N:$FdbAddressCount");

  $graphs['fdb_count'] = TRUE;

  echo("FDB Count ");
}

?>