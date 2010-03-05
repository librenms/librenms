<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

echo("Temperatures : ");

include("temperatures-junose.inc.php");


if($device['os'] == "ironware")
{
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
        if (mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$temp_oid' AND device_id = '$id'"),0) == '0')
        {
          $query = "INSERT INTO temperature (`device_id`, `temp_oid`, `temp_descr`, `temp_limit`, `temp_current`, `temp_precision`) values ('$id', '$temp_oid', '$descr'," . ($config['defaults']['temp_limit'] ? $config['defaults']['temp_limit'] : '60') . ", '$temp', '2')";
          mysql_query($query);
          echo("+");
        } elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `device_id` = '$id' AND `temp_oid` = '$temp_oid'"), 0) != $descr) {
          mysql_query("UPDATE temperature SET `temp_descr` = '$descr' WHERE `device_id` = '$id' AND `temp_oid` = '$temp_oid' AND `temp_precision` = '2'");
          echo("U");
        } else {
          echo(".");
        }
        $temp_exists[] = "$id $temp_oid";
      }
    }
  }
}


## JunOS Temperatures
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
        if (mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$temp_oid' AND device_id = '$id'"),0) == '0')
        {
          $query = "INSERT INTO temperature (`device_id`, `temp_oid`, `temp_descr`, `temp_limit`, `temp_current`) values ('$id', '$temp_oid', '$descr'," . ($config['defaults']['temp_limit'] ? $config['defaults']['temp_limit'] : '60') . ", '$temp')";
          mysql_query($query);
          echo("+");
        } elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `device_id` = '$id' AND `temp_oid` = '$temp_oid'"), 0) != $descr) {
          mysql_query("UPDATE temperature SET `temp_descr` = '$descr' WHERE `device_id` = '$id' AND `temp_oid` = '$temp_oid'");
          echo("U");
        } else { 
          echo("."); 
        }
        $temp_exists[] = "$id $temp_oid";
      }
    }
  }
}


## Papouch TME Temperatures
if ($device['os'] == "papouch-tme") 
{
  echo("Papouch TME ");
  $descr = trim(shell_exec($config['snmpget'] . " -O qv -$snmpver -c $community $hostname:$port SNMPv2-SMI::enterprises.18248.1.1.3.0"));
  $temp = trim(shell_exec($config['snmpget'] . " -O qv -$snmpver -c $community $hostname:$port SNMPv2-SMI::enterprises.18248.1.1.1.0")) / 10;
  if (!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" && $temp != "0") 
  {
    $temp_oid = "SNMPv2-SMI::enterprises.18248.1.1.1.0";
    $descr = trim(str_replace("\"", "", $descr));
    if (mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$temp_oid' AND device_id = '$id'"),0) == '0') 
    {
      $query = "INSERT INTO temperature (`device_id`, `temp_oid`, `temp_descr`, `temp_precision`, `temp_limit`, `temp_current`) values ('$id', '$temp_oid', '$descr',10," . ($config['defaults']['temp_limit'] ? $config['defaults']['temp_limit'] : '60') . ", '$temp')";
      mysql_query($query);
      echo("+");
    } elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `device_id` = '$id' AND `temp_oid` = '$temp_oid'"), 0) != $descr)
    {
      echo("U");
      mysql_query("UPDATE temperature SET `temp_descr` = '$descr' WHERE `device_id` = '$id' AND `temp_oid` = '$temp_oid'");
    } else {
      echo(".");
    }
    $temp_exists[] = "$id $temp_oid";
  }
}

## Observer-Style Temperatures
if ($device['os'] == "linux") 
{
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
      if (!mysql_result(mysql_query("SELECT count(temp_id) FROM temperature WHERE `device_id` = '$id' AND `temp_oid` = '$fulloid'"), 0)) 
      {
        echo("+");
        mysql_query("INSERT INTO `temperature` (`device_id`,`temp_oid`,`temp_descr`,`temp_limit`) VALUES ('$id', '$fulloid', '$descr'," . ($config['defaults']['temp_limit'] ? $config['defaults']['temp_limit'] : '60') . ")");
      } 
      elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `device_id` = '$id' AND `temp_oid` = '$fulloid'"), 0) != $descr) 
      {
        echo("U");
	  mysql_query("UPDATE temperature SET `temp_descr` = '$descr' WHERE `device_id` = '$id' AND `temp_oid` = '$fulloid'");
      }
      else 
      {
        echo(".");
      }
      $temp_exists[] = "$id $fulloid";
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
      if (!mysql_result(mysql_query("SELECT count(temp_id) FROM temperature WHERE `device_id` = '$id' AND `temp_oid` = '$fulloid'"), 0)) 
      {
        mysql_query("INSERT INTO `temperature` (`device_id`,`temp_oid`,`temp_descr`, `temp_precision`, `temp_limit`) VALUES ('$id', '$fulloid', '$descr', '10', " . ($config['defaults']['temp_limit'] ? $config['defaults']['temp_limit'] : '60') . ")");
	  echo("+");
      } 
      elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `device_id` = '$id' AND `temp_oid` = '$fulloid'"), 0) != $descr) 
      {
        mysql_query("UPDATE temperature SET `temp_descr` = '$descr' WHERE `device_id` = '$id' AND `temp_oid` = '$fulloid'");
        echo("UPDATE temperature SET `temp_descr` = '$descr' WHERE `device_id` = '$id' AND `temp_oid` = '$fulloid'");
        echo("U");
      }
      else
      {
        echo(".");
      }
      $temp_exists[] = "$id $fulloid";
    }
  } 
}

## LMSensors Temperatures
if ($device['os'] == "linux") 
{
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
      if (mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$temp_oid' AND device_id = '$id'"),0) == '0') 
      {
        $query = "INSERT INTO temperature (`device_id`, `temp_oid`, `temp_descr`, `temp_precision`, `temp_limit`, `temp_current`) values ('$id', '$temp_oid', '$descr',1000, " . ($config['defaults']['temp_limit'] ? $config['defaults']['temp_limit'] : '60') . ", '$temp')";
        mysql_query($query);
        echo("+");
      } 
      elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `device_id` = '$id' AND `temp_oid` = '$temp_oid'"), 0) != $descr) 
      {
        echo("U");
        mysql_query("UPDATE temperature SET `temp_descr` = '$descr' WHERE `device_id` = '$id' AND `temp_oid` = '$temp_oid'");
      } 
      else 
      {
        echo("."); 
      } 
      $temp_exists[] = "$id $temp_oid";
    }
  }
}

## Supermicro Temperatures
if ($device['os'] == "linux") 
{
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
          if (mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$temp_oid' AND device_id = '$id'"),0) == '0') 
          {
            $query = "INSERT INTO temperature (`device_id`, `temp_oid`, `temp_descr`, `temp_current`, `temp_limit`, `temp_precision`) values ('$id', '$temp_oid', '$descr', '$temp', '$limit','$divisor')";
            mysql_query($query);
            echo("+");
          } 
          else 
          { 
            echo("."); 
          }
          $temp_exists[] = "$id $temp_oid";
        }
      }
    }
  }
}

## Cisco Temperatures
if ($device['os'] == "ios") 
{
  echo("Cisco ");
  $oids = shell_exec($config['snmpwalk'] . " -m CISCO-ENVMON-MIB -$snmpver -CI -Osqn -c $community $hostname:$port .1.3.6.1.4.1.9.9.13.1.3.1.2 | sed s/.1.3.6.1.4.1.9.9.13.1.3.1.2.//g");
  $oids = trim($oids);
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid) = explode(" ", $data);
      $temp_oid  = ".1.3.6.1.4.1.9.9.13.1.3.1.3.$oid";
      $descr_oid = ".1.3.6.1.4.1.9.9.13.1.3.1.2.$oid";
      $descr = trim(shell_exec($config['snmpget'] . " -m CISCO-ENVMON-MIB -O qv -$snmpver -c $community $hostname:$port $descr_oid"));
      $temp  = trim(shell_exec($config['snmpget'] . " -m CISCO-ENVMON-MIB -O qv -$snmpver -c $community $hostname:$port $temp_oid"));
      if (!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" ) 
      {
        $descr = str_replace("\"", "", $descr);
        $descr = str_replace("temperature", "", $descr);
        $descr = str_replace("temp", "", $descr);
        $descr = trim($descr);
        if (mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$temp_oid' AND device_id = '$id'"),0) == '0') 
        {
          $query = "INSERT INTO temperature (`device_id`, `temp_oid`, `temp_descr`, `temp_limit`, `temp_current`) values ('$id', '$temp_oid', '$descr', " . ($config['defaults']['temp_limit'] ? $config['defaults']['temp_limit'] : '60') . ", '$temp')";
          mysql_query($query);
          echo("+");
        }
        else 
        { 
          echo(".");
        }
        $temp_exists[] = "$id $temp_oid";
      }
    }
  } 
}

## Delete removed sensors

$sql = "SELECT * FROM temperature AS T, devices AS D WHERE T.device_id = D.device_id AND D.device_id = '".$device['device_id']."'";

if ($query = mysql_query($sql))
{
  while ($sensor = mysql_fetch_array($query)) 
  {
    unset($exists);
    $i = 0;
    while ($i < count($temp_exists) && !$exists) 
    {
      $thistemp = $sensor['device_id'] . " " . $sensor['temp_oid'];
      if ($temp_exists[$i] == $thistemp) { $exists = 1; }
      $i++;
    }
  
    if (!$exists) 
    { 
      echo("-");
      mysql_query("DELETE FROM temperature WHERE temp_id = '" . $sensor['temp_id'] . "'"); 
    }
  }
}

unset($temp_exists); echo("\n");

?>
