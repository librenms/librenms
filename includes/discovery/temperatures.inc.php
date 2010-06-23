<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

echo("Temperatures : ");

$valid_temp = array();

include("temperatures-junose.inc.php");

switch ($device['os'])
{
  case "ironware":
    echo("IronWare ");
    $oids = shell_exec($config['snmpwalk'] . " -$snmpver -CI -Osqn -m FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB -c $community $hostname:$port snAgentTempSensorDescr");
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
        $descr = trim(shell_exec($config['snmpget'] . " -O qv -$snmpver -c $community $hostname:$port $descr_oid"));
        $temp = trim(shell_exec($config['snmpget'] . " -O qv -$snmpver -c $community $hostname:$port $temp_oid"));
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
    break;

  case "areca":
    $oids = shell_exec($config['snmpwalk'] . " -$snmpver -CI -Osqn -c $community $hostname:$port SNMPv2-SMI::enterprises.18928.1.1.2.14.1.2");
    if ($debug) { echo($oids."\n"); }
    $oids = trim($oids);
    if ($oids) echo("Areca Harddisk ");
    foreach(explode("\n", $oids) as $data) 
    {
      $data = trim($data);
      if ($data) 
      {
        list($oid,$descr) = explode(" ", $data,2);
        $split_oid = explode('.',$oid);
        $temp_id = $split_oid[count($split_oid)-1];
        $temp_oid  = "1.3.6.1.4.1.18928.1.1.2.14.1.2.$temp_id";
        $temp  = trim(shell_exec($config['snmpget'] . " -O qv -$snmpver -c $community $hostname:$port $temp_oid"));
        $descr = "Hard disk $temp_id";
        if ($temp != -128) # -128 = not measured/present
        {
          discover_temperature($valid_temp, $device, $temp_oid, zeropad($temp_id), "areca", $descr, 1, NULL, NULL, $temp);
        }
      }
    }

    $oids = snmp_walk($device, "1.3.6.1.4.1.18928.1.2.2.1.10.1.2", "-OsqnU", "");
    if ($debug) { echo($oids."\n"); }
    if ($oids) echo("Areca Controller ");
    $precision = 1;
    $type = "areca";
    foreach(explode("\n", $oids) as $data) 
    {
      $data = trim($data);
      if ($data) 
      {
        list($oid,$descr) = explode(" ", $data,2);
        $split_oid = explode('.',$oid);
        $index = $split_oid[count($split_oid)-1];
        $oid  = "1.3.6.1.4.1.18928.1.2.2.1.10.1.3." . $index;
        $current = snmp_get($device, $oid, "-Oqv", "");
        discover_temperature($valid_temp, $device, $oid, $index, $type, trim($descr,'"'), $precision, NULL, NULL, $current);
      }
    }
    break;

  case "papouch-tme":
    echo("Papouch TME ");
    $descr = trim(shell_exec($config['snmpget'] . " -O qv -$snmpver -c $community $hostname:$port SNMPv2-SMI::enterprises.18248.1.1.3.0"));
    $temp = trim(shell_exec($config['snmpget'] . " -O qv -$snmpver -c $community $hostname:$port SNMPv2-SMI::enterprises.18248.1.1.1.0")) / 10;
    if (!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" && $temp != "0") 
    {
      $temp_oid = ".1.3.6.1.4.1.18248.1.1.1.0";
      $descr = trim(str_replace("\"", "", $descr));
      discover_temperature($valid_temp, $device, $temp_oid, "1", "ironware", $descr, "10", NULL, NULL, $temp);
    }
    break;

  case "linux": 
    # Observer-style temperature
    $oids = shell_exec($config['snmpwalk'] . " -$snmpver -m SNMPv2-SMI -Osqn -CI -c $community $hostname:$port .1.3.6.1.4.1.2021.7891 | sed s/.1.3.6.1.4.1.2021.7891.// | grep '.1.1 ' | grep -v '.101.' | cut -d'.' -f 1");
    $oids = trim($oids);
    if ($oids) echo("Observer-Style ");
    foreach(explode("\n",$oids) as $oid) 
    {
      $oid = trim($oid);
      if ($oid != "") 
      {
        $descr_query = $config['snmpget'] . " -$snmpver -m SNMPv2-SMI -Osqn -c $community $hostname:$port .1.3.6.1.4.1.2021.7891.$oid.2.1 | sed s/.1.3.6.1.4.1.2021.7891.$oid.2.1\ //";
        $descr = trim(str_replace("\"", "", shell_exec($descr_query)));
        $fulloid = ".1.3.6.1.4.1.2021.7891.$oid.101.1";
        discover_temperature($valid_temp, $device, $fulloid, $oid, "observer", $descr, "1", NULL, NULL, NULL);
      }
    }

    # LM-Sensors
    $oids = shell_exec($config['snmpwalk'] . " -m LM-SENSORS-MIB -$snmpver -CI -Osqn -c $community $hostname:$port lmTempSensorsDevice");
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
        $temp  = trim(shell_exec($config['snmpget'] . " -m LM-SENSORS-MIB -O qv -$snmpver -c $community $hostname:$port $temp_oid")) / 1000;
        $descr = str_ireplace("temp-", "", $descr);
        $descr = trim($descr);
        if($temp != "0" && $temp <= "1000") 
        {
          discover_temperature($valid_temp, $device, $temp_oid, $temp_id, "lmsensors", $descr, "1000", NULL, NULL, $temp);
        }
      }
    }
    
    # Supermicro sensors
    $oids = shell_exec($config['snmpwalk'] . " -m SUPERMICRO-HEALTH-MIB -$snmpver -CI -Osqn -c $community $hostname:$port 1.3.6.1.4.1.10876.2.1.1.1.1.3 | sed s/1.3.6.1.4.1.10876.2.1.1.1.1.3.//g");
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
          $descr   = trim(shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $descr_oid"));
          $temp    = trim(shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $temp_oid"));
          $limit   = trim(shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $limit_oid"));
          $divisor = trim(shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $divisor_oid"));
          $monitor = trim(shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $monitor_oid"));
          if ($monitor == 'true')
          {
            $descr = trim(str_ireplace("temperature", "", $descr));
            discover_temperature($valid_temp, $device, $temp_oid, trim($oid,'.'), "supermicro", $descr, $divisor, $limit, NULL, $temp);
          }
        }
      }
    }
    break;

  case "ios":
    echo("Cisco ");
    $oids = shell_exec($config['snmpwalk'] . " -m CISCO-ENVMON-MIB -$snmpver -CI -Osqn -c $community $hostname:$port .1.3.6.1.4.1.9.9.13.1.3.1.2 | sed s/.1.3.6.1.4.1.9.9.13.1.3.1.2.//g");
    $oids = trim($oids);
    foreach(explode("\n", $oids) as $data) 
    {
      $data = trim($data);
      if ($data) 
      {
        list($index) = explode(" ", $data);
        $oid  = ".1.3.6.1.4.1.9.9.13.1.3.1.3.$index";
        $descr_oid = ".1.3.6.1.4.1.9.9.13.1.3.1.2.$index";
        $descr = snmp_get($device, $descr_oid, "-Oqv", "CISCO-ENVMON-MIB");
        $temp = snmp_get($device, $oid, "-Oqv", "CISCO-ENVMON-MIB");
        if (!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" ) 
        {
          $descr = str_replace("\"", "", $descr);
          $descr = str_replace("temperature", "", $descr);
          $descr = str_replace("temp", "", $descr);
          $descr = trim($descr);
          discover_temperature($valid_temp, $device, $oid, $index, "cisco", $descr, "1", NULL, NULL, $temp);
        }
      }
    } 
    break;
    
  case "netmanplus":
    $oids = snmp_walk($device, "1.3.6.1.2.1.33.1.2.7", "-Osqn", "UPS-MIB");
    if ($debug) { echo($oids."\n"); }
    $oids = trim($oids);
    if ($oids) echo("NetMan Plus Battery Temperature ");
    foreach(explode("\n", $oids) as $data) 
    {
      $data = trim($data);
      if ($data) 
      {
        list($oid,$descr) = explode(" ", $data,2);
        $split_oid = explode('.',$oid);
        $temp_id = $split_oid[count($split_oid)-1];
        $temp_oid  = "1.3.6.1.2.1.33.1.2.7.$temp_id";
        $temp  = trim(shell_exec($config['snmpget'] . " -O qv -$snmpver -c $community $hostname:$port $temp_oid"));
        $descr = "Battery" . (count(explode("\n",$oids)) == 1 ? '' : ' ' . ($temp_id+1));
        discover_temperature($valid_temp, $device, $temp_oid, $temp_id, "netmanplus", $descr, 1, NULL, NULL, $temp);
      }
    }
    break;

  case "akcp":
  case "minkelsrms":
    $oids = snmp_walk($device, ".1.3.6.1.4.1.3854.1.2.2.1.16.1.4", "-Osqn", "");
    if ($debug) { echo($oids."\n"); }
    $oids = trim($oids);
    if ($oids) echo("AKCP ");
    foreach(explode("\n", $oids) as $data) 
    {
      $data = trim($data);
      if ($data) 
      {
        list($oid,$status) = explode(" ", $data,2);
        if ($status == 2) # 2 = normal, 0 = not connected
        {
          $split_oid = explode('.',$oid);
          $temp_id = $split_oid[count($split_oid)-1];
          $descr_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.1.$temp_id";
          $temp_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.3.$temp_id";
          $descr = trim(snmp_get($device, $descr_oid, "-Oqv", ""),'"');
          $temp = snmp_get($device, $temp_oid, "-Oqv", "");
        
          discover_temperature($valid_temp, $device, $temp_oid, $temp_id, "akcp", $descr, 1, NULL, NULL, $temp);
        }
      }
    }
    break;
}

if ($device['os'] == "junos" || $device['os_group'] == "junos") 
{
  echo("JunOS ");
  $oids = shell_exec($config['snmpwalk'] . " -M +".$config['install_dir']."/mibs/junos -m JUNIPER-MIB -$snmpver -CI -Osqn -c $community $hostname:$port 1.3.6.1.4.1.2636.3.1.13.1.7");
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
      $descr = trim(shell_exec($config['snmpget'] . " -M +".$config['install_dir']."/mibs/junos -m JUNIPER-MIB -O qv -$snmpver -c $community $hostname:$port $descr_oid"));
      $temp = trim(shell_exec($config['snmpget'] . " -M +".$config['install_dir']."/mibs/junos -m JUNIPER-MIB -O qv -$snmpver -c $community $hostname:$port $temp_oid"));
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

## Dell Temperatures
if (strstr($device['hardware'], "dell")) 
{
  $oids = shell_exec($config['snmpwalk'] . " -m MIB-Dell-10892 -$snmpver -CI -Osqn -c $community $hostname:$port .1.3.6.1.4.1.674.10892.1.700.20.1.8");
  $oids = trim($oids);
  if ($oids) echo("Dell OMSA ");
  foreach(explode("\n",$oids) as $oid) 
  {
    $oid = substr(trim($oid), 36);
    list($oid) = explode(" ", $oid);
    if ($oid != "") 
    {
      $descr_query = $config['snmpget'] . " -m MIB-Dell-10892 -$snmpver  -Onvq -c $community $hostname:$port .1.3.6.1.4.1.674.10892.1.700.20.1.8.$oid";
      $descr = trim(str_replace("\"", "", shell_exec($descr_query)));
      $fulloid = ".1.3.6.1.4.1.674.10892.1.700.20.1.6.$oid";
      discover_temperature($valid_temp, $device, $fulloid, $oid, "dell", $descr, "10", NULL, NULL, NULL);
    }
  } 
}

if($debug) { print_r($valid_temp); }

$sql = "SELECT * FROM temperature AS T, devices AS D WHERE T.device_id = D.device_id AND D.device_id = '".$device['device_id']."'";
if ($query = mysql_query($sql))
{
  while ($test_temperature = mysql_fetch_array($query)) 
  {
    $temperature_index = $test_temperature['temp_index'];
    $temperature_type = $test_temperature['temp_type'];
    if($debug) { echo($temperature_index . " -> " . $temperature_type . "\n"); }
    if(!$valid_temp[$temperature_type][$temperature_index]) 
    {
      echo("-");
      mysql_query("DELETE FROM `temperature` WHERE temp_id = '" . $test_temperature['temp_id'] . "'");
    }
    unset($temperature_oid); unset($temperature_type);
  }
}

unset($valid_temp); echo("\n");

?>
