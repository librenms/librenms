<?php

if($device['os'] == "linux")
{

    # Supermicro sensors
    ## FIX ME snmp_walk snmp_get needed
    ## Fix this shit if you can test it.
    $oids = snmp_walk($device, "1.3.6.1.4.1.10876.2.1.1.1.1.3", "-Osqn", "SUPERMICRO-HEALTH-MIB");

    $oids = shell_exec($config['snmpwalk'] . " -M " . $config['mibdir'] . " -m SUPERMICRO-HEALTH-MIB -$snmpver -CI -Osqn -c $community $hostname:$port 1.3.6.1.4.1.10876.2.1.1.1.1.3 | sed s/1.3.6.1.4.1.10876.2.1.1.1.1.3.//g");
    $oids = trim($oids);
    if ($oids) echo("Supermicro ");
    foreach(explode("\n", $oids) as $data)
    {
      $data = trim($data);
      if ($data)
      {
        list($oid,$type) = explode(" ", $data);
        if ($type == 2)
        {
          $temp_oid    = "1.3.6.1.4.1.10876.2.1.1.1.1.4$oid";
          $descr_oid   = "1.3.6.1.4.1.10876.2.1.1.1.1.2$oid";
          $limit_oid   = "1.3.6.1.4.1.10876.2.1.1.1.1.5$oid";
          $divisor_oid = "1.3.6.1.4.1.10876.2.1.1.1.1.9$oid";
          $monitor_oid = "1.3.6.1.4.1.10876.2.1.1.1.1.10$oid";
          $descr   = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $descr_oid"));
          $temp    = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $temp_oid"));
          $limit   = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $limit_oid"));
          $divisor = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $divisor_oid"));
          $monitor = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $monitor_oid"));
          if ($monitor == 'true')
          {
            $descr = trim(str_ireplace("temperature", "", $descr));
            discover_temperature($valid_temp, $device, $temp_oid, trim($oid,'.'), "supermicro", $descr, $divisor, $limit, NULL, $temp);
          }
        }
      }
    }
}

?>
