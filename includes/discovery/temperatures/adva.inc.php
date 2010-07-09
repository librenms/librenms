<?php
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
    $temp_chassis = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -O qv -$snmpver -c $community $hostname:$port $oid_chassis"));
    $temp_stm16 = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -O qv -$snmpver -c $community $hostname:$port $oid_stm16"));
    $temp_hss1 = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -O qv -$snmpver -c $community $hostname:$port $oid_hss1"));
    $temp_hss2 = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -O qv -$snmpver -c $community $hostname:$port $oid_hss2"));
    echo("Adva Chassis ");
    if($temp_chassis != "0")
    {
      if(mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$oid_chassis' AND temp_host = '$id'"),0) == '0') 
      {
        $query = "INSERT INTO temperature (`temp_host`, `temp_oid`, `temp_descr`, `temp_precision`, `temp_limit`, `temp_current`) values ('$id', '$oid_chassis', '$descr_chassis',1," . ($config['defaults']['temp_limit'] ? $config['defaults']['temp_limit'] : '60') . ", '$temp_chassis')";
        mysql_query($query);
        echo("+");
      } elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `temp_host` = '$id' AND `temp_oid` = '$oid_chassis'"), 0) != $descr_chassis) {
        echo("U");
        mysql_query("UPDATE temperature SET `temp_descr` = '$descr_chassis' WHERE `temp_host` = '$id' AND `temp_oid` = '$oid_chassis'");
      } else {
        echo(".");
      }
      $temp_exists[] = "$id $oid_chassis";
    }
    echo("STM16 ");
    if($temp_stm16 != "0")
    {
      if(mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$oid_stm16' AND temp_host = '$id'"),0) == '0')
      {
       $query = "INSERT INTO temperature (`temp_host`, `temp_oid`, `temp_descr`, `temp_precision`, `temp_limit`, `temp_current`) values ('$id', '$oid_stm16', '$descr_stm16',1," . ($config['defaults']['temp_limit'] ? $config['defaults']['temp_limit'] : '60') . ", '$temp_stm16')";
       mysql_query($query);
       echo("+");
      } elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `temp_host` = '$id' AND `temp_oid` = '$oid_stm16'"), 0) != $descr_stm16) {
        echo("U");
        mysql_query("UPDATE temperature SET `temp_descr` = '$descr_stm16' WHERE `temp_host` = '$id' AND `temp_oid` = '$oid_stm16'");
      } else {
        echo(".");
      }
      $temp_exists[] = "$id $oid_stm16";
    }
    echo("HSS1 ");
    if($temp_hss1 != "0")
    {
      if(mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$oid_hss1' AND temp_host = '$id'"),0) == '0')
      {
       $query = "INSERT INTO temperature (`temp_host`, `temp_oid`, `temp_descr`, `temp_precision`, `temp_limit`, `temp_current`) values ('$id', '$oid_hss1', '$descr_hss1',1," . ($config['defaults']['temp_limit'] ? $config['defaults']['temp_limit'] : '60') . ", '$temp_hss1')";
       mysql_query($query);
       echo("+");
      } elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `temp_host` = '$id' AND `temp_oid` = '$oid_hss1'"), 0) != $descr_hss1) {
        echo("U");
        mysql_query("UPDATE temperature SET `temp_descr` = '$descr_hss1' WHERE `temp_host` = '$id' AND `temp_oid` = '$oid_hss1'");
      } else {
        echo(".");
      }
      $temp_exists[] = "$id $oid_hss1";
    }
    echo("HSS2 ");
    if($temp_hss2 != "0")
    {
      if(mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$oid_hss2' AND temp_host = '$id'"),0) == '0')
      {
       $query = "INSERT INTO temperature (`temp_host`, `temp_oid`, `temp_descr`, `temp_precision`, `temp_limit`, `temp_current`) values ('$id', '$oid_hss2', '$descr_hss2',1," . ($config['defaults']['temp_limit'] ? $config['defaults']['temp_limit'] : '60') . ", '$temp_hss2')";
       mysql_query($query);
       echo("+");
      } elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `temp_host` = '$id' AND `temp_oid` = '$oid_hss2'"), 0) != $descr_hss2) {
        echo("U");
        mysql_query("UPDATE temperature SET `temp_descr` = '$descr_hss2' WHERE `temp_host` = '$id' AND `temp_oid` = '$oid_hss2'");
      } else {
        echo(".");
      }
      $temp_exists[] = "$id $oid_hss2";
    }
*/
?>
