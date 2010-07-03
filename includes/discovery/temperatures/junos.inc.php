<?php

if ($device['os'] == "junos" || $device['os_group'] == "junos")
{
  echo("JunOS ");
  $oids = shell_exec($config['snmpwalk'] . " -M " . $config['mibdir'] . " -M +".$config['install_dir']."/mibs/junos -m JUNIPER-MIB -$snmpver -CI -Osqn -c $community $hostname:$port 1.3.6.1.4.1.2636.3.1.13.1.7");
  $oids = trim($oids);
  foreach(explode("\n", $oids) as $data)
  {
    $data = trim($data);
    $data = substr($data, 29);
    if ($data)
    {
      list($oid) = explode(" ", $data);
      $temp_oid  = "1.3.6.1.4.1.2636.3.1.13.1.7.$oid";
      $descr_oid = "1.3.6.1.4.1.2636.3.1.13.1.5.$oid";
      $descr = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -M +".$config['install_dir']."/mibs/junos -m JUNIPER-MIB -O qv -$snmpver -c $community $hostname:$port $descr_oid"));
      $temp = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -M +".$config['install_dir']."/mibs/junos -m JUNIPER-MIB -O qv -$snmpver -c$community $hostname:$port $temp_oid"));
      if (!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" && $temp != "0")
      {
        $descr = str_replace("\"", "", $descr);
        $descr = str_replace("temperature", "", $descr);
        $descr = str_replace("temp", "", $descr);
        $descr = str_replace("sensor", "", $descr);
        $descr = trim($descr);

        discover_temperature($valid_temp, $device, $temp_oid, $oid, "junos", $descr, "1", NULL, NULL, $temp);

      }
    }
  }
}


?>
