<?php

if($device['os'] == "linux")
{
    ## FIX ME SNMP_WALK
    $oids = shell_exec($config['snmpwalk'] . " -M " . $config['mibdir'] . " -m LM-SENSORS-MIB -$snmpver -CI -Osqn -c $community $hostname:$port lmTempSensorsDevice");
    if ($debug) { echo($oids."\n"); }
    $oids = trim($oids);
    if ($oids) echo("LM-SENSORS ");
    foreach(explode("\n", $oids) as $data)
    {
      $data = trim($data);
      if ($data)
      {
        list($oid,$descr) = explode(" ", $data,2);
        $split_oid = explode('.',$oid);
        $temp_id = $split_oid[count($split_oid)-1];
        $temp_oid  = "1.3.6.1.4.1.2021.13.16.2.1.3.$temp_id";
        $temp = snmp_get($device, $temp_oid, "-Ovq") / 1000;
        $descr = str_ireplace("temp-", "", $descr);
        $descr = trim($descr);
        if($temp != "0" && $temp <= "1000")
        {
          discover_temperature($valid_temp, $device, $temp_oid, $temp_id, "lmsensors", $descr, "1000", NULL, NULL, $temp);
        }
      }
    }


}

?>
