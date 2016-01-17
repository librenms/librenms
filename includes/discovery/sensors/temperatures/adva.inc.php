<?php

// MYSQL Check - FIXME
// 5 SELECTS
// 4 INSERTS
// 4 UPDATES
/*
    Disabled needing rewrite

    $id = $device['device_id'];
    $hostname = $device['hostname'];
    $community = $device['community'];
    $snmpver = $device['snmpver'];
    $port = $device['port'];

    $oid_chassis = "1.3.6.1.4.1.2544.1.9.2.4.1.2.1.1.1";
    $descr_chassis = "chassis";
    $oid_stm16 = "1.3.6.1.4.1.2544.1.9.2.4.1.5.1.1.12";
    $descr_stm16 = "stm16";
    $oid_hss1 = "1.3.6.1.4.1.2544.1.9.2.4.1.5.1.1.13";
    $descr_hss1 = "hss1";
    $oid_hss2 = "1.3.6.1.4.1.2544.1.9.2.4.1.5.1.1.14";
    $descr_hss2 = "hss2";
    $temperature_chassis = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -O qv -$snmpver -c $community $hostname:$port $oid_chassis"));
    $temperature_stm16 = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -O qv -$snmpver -c $community $hostname:$port $oid_stm16"));
    $temperature_hss1 = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -O qv -$snmpver -c $community $hostname:$port $oid_hss1"));
    $temperature_hss2 = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -O qv -$snmpver -c $community $hostname:$port $oid_hss2"));
    echo("Adva Chassis ");
    if ($temperature_chassis != "0")
    {
      if (mysql_result(mysql_query("SELECT count(temperature_id) FROM `temperature` WHERE temperature_oid = '$oid_chassis' AND temperature_host = '$id'"),0) == '0')
      {
        $query = "INSERT INTO temperature (`temperature_host`, `temperature_oid`, `temperature_descr`, `temperature_precision`, `temperature_limit`, `temperature_current`) values ('$id', '$oid_chassis', '$descr_chassis',1," . ($config['defaults']['temperature_limit'] ? $config['defaults']['temperature_limit'] : '60') . ", '$temperature_chassis')";
        mysql_query($query);
        echo("+");
      } elseif (mysql_result(mysql_query("SELECT `temperature_descr` FROM temperature WHERE `temperature_host` = '$id' AND `temperature_oid` = '$oid_chassis'"), 0) != $descr_chassis) {
        echo("U");
        mysql_query("UPDATE temperature SET `temperature_descr` = '$descr_chassis' WHERE `temperature_host` = '$id' AND `temperature_oid` = '$oid_chassis'");
      } else {
        echo(".");
      }
      $temperature_exists[] = "$id $oid_chassis";
    }
    echo("STM16 ");
    if ($temperature_stm16 != "0")
    {
      if (mysql_result(mysql_query("SELECT count(temperature_id) FROM `temperature` WHERE temperature_oid = '$oid_stm16' AND temperature_host = '$id'"),0) == '0')
      {
       $query = "INSERT INTO temperature (`temperature_host`, `temperature_oid`, `temperature_descr`, `temperature_precision`, `temperature_limit`, `temperature_current`) values ('$id', '$oid_stm16', '$descr_stm16',1," . ($config['defaults']['temperature_limit'] ? $config['defaults']['temperature_limit'] : '60') . ", '$temperature_stm16')";
       mysql_query($query);
       echo("+");
      } elseif (mysql_result(mysql_query("SELECT `temperature_descr` FROM temperature WHERE `temperature_host` = '$id' AND `temperature_oid` = '$oid_stm16'"), 0) != $descr_stm16) {
        echo("U");
        mysql_query("UPDATE temperature SET `temperature_descr` = '$descr_stm16' WHERE `temperature_host` = '$id' AND `temperature_oid` = '$oid_stm16'");
      } else {
        echo(".");
      }
      $temperature_exists[] = "$id $oid_stm16";
    }
    echo("HSS1 ");
    if ($temperature_hss1 != "0")
    {
      if (mysql_result(mysql_query("SELECT count(temperature_id) FROM `temperature` WHERE temperature_oid = '$oid_hss1' AND temperature_host = '$id'"),0) == '0')
      {
       $query = "INSERT INTO temperature (`temperature_host`, `temperature_oid`, `temperature_descr`, `temperature_precision`, `temperature_limit`, `temperature_current`) values ('$id', '$oid_hss1', '$descr_hss1',1," . ($config['defaults']['temperature_limit'] ? $config['defaults']['temperature_limit'] : '60') . ", '$temperature_hss1')";
       mysql_query($query);
       echo("+");
      } elseif (mysql_result(mysql_query("SELECT `temperature_descr` FROM temperature WHERE `temperature_host` = '$id' AND `temperature_oid` = '$oid_hss1'"), 0) != $descr_hss1) {
        echo("U");
        mysql_query("UPDATE temperature SET `temperature_descr` = '$descr_hss1' WHERE `temperature_host` = '$id' AND `temperature_oid` = '$oid_hss1'");
      } else {
        echo(".");
      }
      $temperature_exists[] = "$id $oid_hss1";
    }
    echo("HSS2 ");
    if ($temperature_hss2 != "0")
    {
      if (mysql_result(mysql_query("SELECT count(temperature_id) FROM `temperature` WHERE temperature_oid = '$oid_hss2' AND temperature_host = '$id'"),0) == '0')
      {
       $query = "INSERT INTO temperature (`temperature_host`, `temperature_oid`, `temperature_descr`, `temperature_precision`, `temperature_limit`, `temperature_current`) values ('$id', '$oid_hss2', '$descr_hss2',1," . ($config['defaults']['temperature_limit'] ? $config['defaults']['temperature_limit'] : '60') . ", '$temperature_hss2')";
       mysql_query($query);
       echo("+");
      } elseif (mysql_result(mysql_query("SELECT `temperature_descr` FROM temperature WHERE `temperature_host` = '$id' AND `temperature_oid` = '$oid_hss2'"), 0) != $descr_hss2) {
        echo("U");
        mysql_query("UPDATE temperature SET `temperature_descr` = '$descr_hss2' WHERE `temperature_host` = '$id' AND `temperature_oid` = '$oid_hss2'");
      } else {
        echo(".");
      }
      $temperature_exists[] = "$id $oid_hss2";
    }
*/
