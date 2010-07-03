<?php

  if($device['os_group'] == "unix") {

    # Observer-style temperature
    $oids = shell_exec($config['snmpwalk'] . " -M " . $config['mibdir'] . " -M " . $config['mibdir'] . " -$snmpver -m SNMPv2-SMI -Osqn -CI -c $community $hostname:$port .1.3.6.1.4.1.2021.7891 | sed s/.1.3.6.1.4.1.2021.7891.// | grep '.1.1 ' | grep -v '.101.' | cut -d'.' -f 1");
    $oids = trim($oids);
    if ($oids) echo("Observer-Style ");
    foreach(explode("\n",$oids) as $oid)
    {
      $oid = trim($oid);
      if ($oid != "")
      {
        $descr_query = $config['snmpget'] . " -M " . $config['mibdir'] . " -$snmpver -m SNMPv2-SMI -Osqn -c $community $hostname:$port .1.3.6.1.4.1.2021.7891.$oid.2.1 | sed s/.1.3.6.1.4.1.2021.7891.$oid.2.1\ //";
        $descr = trim(str_replace("\"", "", shell_exec($descr_query)));
        $fulloid = ".1.3.6.1.4.1.2021.7891.$oid.101.1";
        discover_temperature($valid_temp, $device, $fulloid, $oid, "observer", $descr, "1", NULL, NULL, NULL);
      }
    }
  }

?>
