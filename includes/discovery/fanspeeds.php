<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

echo("Fanspeeds : ");

## LMSensors Fanspeeds
/*
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
      $fan_id = $split_oid[count($split_oid)-1];
      $fan_oid  = "1.3.6.1.4.1.2021.13.16.2.1.3.$fan_id";
      $fan  = trim(shell_exec($config['snmpget'] . " -m LM-SENSORS-MIB -O qv -$snmpver -c $community $hostname:$port $fan_oid")) / 1000;
      $descr = str_ireplace("temp-", "", $descr);
      $descr = trim($descr);
      if (mysql_result(mysql_query("SELECT count(fan_id) FROM `fanspeed` WHERE fan_oid = '$fan_oid' AND fan_host = '$id'"),0) == '0') 
      {
        $query = "INSERT INTO fanspeed (`fan_host`, `fan_oid`, `fan_descr`, `fan_precision`, `fan_limit`, `fan_current`) values ('$id', '$fan_oid', '$descr',1000, " . ($config['defaults']['fan_limit'] ? $config['defaults']['fan_limit'] : '60') . ", '$fan')";
        mysql_query($query);
        echo("+");
      } 
      elseif (mysql_result(mysql_query("SELECT `fan_descr` FROM fanspeed WHERE `fan_host` = '$id' AND `fan_oid` = '$fan_oid'"), 0) != $descr) 
      {
        echo("U");
        mysql_query("UPDATE fanspeed SET `fan_descr` = '$descr' WHERE `fan_host` = '$id' AND `fan_oid` = '$fan_oid'");
      } 
      else 
      {
        echo("."); 
      } 
      $fan_exists[] = "$id $fan_oid";
    }
  }
}
*/

## Supermicro Fanspeeds
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
      if ($type == 0)
      {
        $fan_oid     = "1.3.6.1.4.1.10876.2.1.1.1.1.4$oid";
        $descr_oid   = "1.3.6.1.4.1.10876.2.1.1.1.1.2$oid";
        $limit_oid   = "1.3.6.1.4.1.10876.2.1.1.1.1.6$oid";
        $divisor_oid = "1.3.6.1.4.1.10876.2.1.1.1.1.9$oid";
        $monitor_oid = "1.3.6.1.4.1.10876.2.1.1.1.1.10$oid";
        $descr   = trim(shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $descr_oid"));
        $fan     = trim(shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $fan_oid"));
        $limit   = trim(shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $limit_oid"));
        $divisor = trim(shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $divisor_oid"));
        $monitor = trim(shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $monitor_oid"));
        if ($monitor == 'true')
        {
          $descr = trim(str_replace(" Fan","",str_ireplace("Speed", "", $descr)));
          if (mysql_result(mysql_query("SELECT count(fan_id) FROM `fanspeed` WHERE fan_oid = '$fan_oid' AND fan_host = '$id'"),0) == '0') 
          {
            $query = "INSERT INTO fanspeed (`fan_host`, `fan_oid`, `fan_descr`, `fan_current`, `fan_limit`) values ('$id', '$fan_oid', '$descr', '$fan', '$limit')";
            mysql_query($query);
            echo("+");
          } 
          else 
          { 
            echo("."); 
          }
          $fan_exists[] = "$id $fan_oid";
        }
      }
    }
  }
}

## Delete removed sensors

$sql = "SELECT * FROM fanspeed AS V, devices AS D WHERE V.fan_host = D.device_id AND D.device_id = '".$device['device_id']."'";
if ($query = mysql_query($sql))
{
  while ($sensor = mysql_fetch_array($query)) 
  {
    unset($exists);
    $i = 0;
    while ($i < count($fan_exists) && !$exists) 
    {
      $thisfan = $sensor['fan_host'] . " " . $sensor['fan_oid'];
      if ($fan_exists[$i] == $thisfan) { $exists = 1; }
      $i++;
    }
    
    if (!$exists) 
    { 
      echo("-");
      mysql_query("DELETE FROM fanspeed WHERE fan_id = '" . $sensor['fan_id'] . "'"); 
    }
  }
}

unset($fan_exists); echo("\n");

?>
