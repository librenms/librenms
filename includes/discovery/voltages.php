<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

echo("Voltages : ");

## LMSensors Voltages
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
      $volt_id = $split_oid[count($split_oid)-1];
      $volt_oid  = "1.3.6.1.4.1.2021.13.16.2.1.3.$volt_id";
      $volt  = trim(shell_exec($config['snmpget'] . " -m LM-SENSORS-MIB -O qv -$snmpver -c $community $hostname:$port $volt_oid")) / 1000;
      $descr = str_ireplace("temp-", "", $descr);
      $descr = trim($descr);
      if (mysql_result(mysql_query("SELECT count(volt_id) FROM `voltage` WHERE volt_oid = '$volt_oid' AND volt_host = '$id'"),0) == '0') 
      {
        $query = "INSERT INTO voltage (`volt_host`, `volt_oid`, `volt_descr`, `volt_precision`, `volt_limit`, `volt_current`) values ('$id', '$volt_oid', '$descr',1000, " . ($config['defaults']['volt_limit'] ? $config['defaults']['volt_limit'] : '60') . ", '$volt')";
        mysql_query($query);
        echo("+");
      } 
      elseif (mysql_result(mysql_query("SELECT `volt_descr` FROM voltage WHERE `volt_host` = '$id' AND `volt_oid` = '$volt_oid'"), 0) != $descr) 
      {
        echo("U");
        mysql_query("UPDATE voltage SET `volt_descr` = '$descr' WHERE `volt_host` = '$id' AND `volt_oid` = '$volt_oid'");
      } 
      else 
      {
        echo("."); 
      } 
      $volt_exists[] = "$id $volt_oid";
    }
  }
}
*/

## Supermicro Voltages
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
      if ($type == 1)
      {
        $volt_oid  = "1.3.6.1.4.1.10876.2.1.1.1.1.4$oid";
        $descr_oid = "1.3.6.1.4.1.10876.2.1.1.1.1.2$oid";
        $descr = trim(shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $descr_oid"));
        $volt  = trim(shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $volt_oid"));
        $descr = str_ireplace("Voltage", "", $descr);
        $descr = trim($descr);
        if (mysql_result(mysql_query("SELECT count(volt_id) FROM `voltage` WHERE volt_oid = '$volt_oid' AND volt_host = '$id'"),0) == '0') 
        {
          $query = "INSERT INTO voltage (`volt_host`, `volt_oid`, `volt_descr`, `volt_current`, `volt_limit`) values ('$id', '$volt_oid', '$descr', '$volt', '-1')";
          mysql_query($query);
          echo("+");
        } 
        else 
        { 
          echo("."); 
        }
        $volt_exists[] = "$id $volt_oid";
      }
    }
  }
}

## Delete removed sensors

$sql = "SELECT * FROM voltage AS V, devices AS D WHERE V.volt_host = D.device_id AND D.device_id = '".$device['device_id']."'";
$query = mysql_query($sql);

while ($sensor = mysql_fetch_array($query)) 
{
  unset($exists);
  $i = 0;
  while ($i < count($volt_exists) && !$exists) 
  {
    $thisvolt = $sensor['volt_host'] . " " . $sensor['volt_oid'];
    if ($volt_exists[$i] == $thisvolt) { $exists = 1; }
    $i++;
  }
  
  if (!$exists) 
  { 
    echo("-");
    mysql_query("DELETE FROM voltage WHERE volt_id = '" . $sensor['volt_id'] . "'"); 
  }
}

unset($volt_exists); echo("\n");

?>
