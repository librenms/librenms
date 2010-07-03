<?php

  if($device['os'] == "ironware") {
    echo("IronWare ");
    $oids = shell_exec($config['snmpwalk'] . " -M " . $config['mibdir'] . " -$snmpver -CI -Osqn -m FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB -c $community $hostname:$port snAgentTempSensorDescr");
    $oids = trim($oids);
    $oids = str_replace(".1.3.6.1.4.1.1991.1.1.2.13.1.1.3.", "", $oids);
    foreach(explode("\n", $oids) as $data)
    {
      $data = trim($data);
      if ($data != "")
      {
        list($oid) = explode(" ", $data);
        $temp_oid  = ".1.3.6.1.4.1.1991.1.1.2.13.1.1.4.$oid";
        $descr_oid = ".1.3.6.1.4.1.1991.1.1.2.13.1.1.3.$oid";
        $descr = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -O qv -$snmpver -c $community $hostname:$port $descr_oid"));
        $temp = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -O qv -$snmpver -c $community $hostname:$port $temp_oid"));
        if (!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" && $temp != "0")
        {
          $descr = str_replace("\"", "", $descr);
          $descr = str_replace("temperature", "", $descr);
          $descr = str_replace("temp", "", $descr);
          $descr = str_replace("sensor", "Sensor", $descr);
          $descr = str_replace("Line module", "Slot", $descr);
          $descr = str_replace("Switch Fabric module", "Fabric", $descr);
          $descr = str_replace("Active management module", "Mgmt Module", $descr);
          $descr = str_replace("  ", " ", $descr);
          $descr = trim($descr);
          discover_temperature($valid_temp, $device, $temp_oid, $oid, "ironware", $descr, "2", NULL, NULL, $temp);
        }
      }
    }
  }

?>
