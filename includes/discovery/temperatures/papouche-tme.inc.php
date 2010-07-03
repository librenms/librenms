<?php

  if($device['os'] == "papouche-tme") {

    echo("Papouch TME ");
    #$descr = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -O qv -$snmpver -c $community $hostname:$port SNMPv2-SMI::enterprises.18248.1.1.3.0"));
    #$temp = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -O qv -$snmpver -c $community $hostname:$port SNMPv2-SMI::enterprises.18248.1.1.1.0")) / 10;

    $descr = snmp_get($device, "SNMPv2-SMI::enterprises.18248.1.1.3.0", "-Oqv");
    $temp  = snmp_get($device, "SNMPv2-SMI::enterprises.18248.1.1.1.0", "-Oqv") / 10;

    if (!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" && $temp != "0")
    {
      $temp_oid = ".1.3.6.1.4.1.18248.1.1.1.0";
      $descr = trim(str_replace("\"", "", $descr));
      discover_temperature($valid_temp, $device, $temp_oid, "1", "ironware", $descr, "10", NULL, NULL, $temp);
    }

  }

?>
