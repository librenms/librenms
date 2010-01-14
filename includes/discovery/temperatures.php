<?php
  $id = $device['device_id'];
  $hostname = $device['hostname'];
  $community = $device['community'];
  $snmpver = $device['snmpver'];
  $port = $device['port'];

  echo("Temperatures : ");

  ## JunOS Temperatures
  if($device['os'] == "junos") {
    echo("JunOS ");
    $oids = shell_exec($config['snmpwalk'] . " -m JUNIPER-MIB -$snmpver -CI -Osqn -c $community $hostname:$port 1.3.6.1.4.1.2636.3.1.13.1.7");
    $oids = trim($oids);
    foreach(explode("\n", $oids) as $data) {
     $data = trim($data);
     $data = substr($data, 29);
     if($data) {
      list($oid) = explode(" ", $data);
      $temp_oid  = "1.3.6.1.4.1.2636.3.1.13.1.7.$oid";
      $descr_oid = "1.3.6.1.4.1.2636.3.1.13.1.5.$oid";
      $descr = trim(shell_exec($config['snmpget'] . " -m JUNIPER-MIB -O qv -$snmpver -c $community $hostname:$port $descr_oid"));
      $temp = trim(shell_exec($config['snmpget'] . " -m JUNIPER-MIB -O qv -$snmpver -c $community $hostname:$port $temp_oid"));
      if(!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" && $temp != "0") {
        $descr = str_replace("\"", "", $descr);
        $descr = str_replace("temperature", "", $descr);
        $descr = str_replace("temp", "", $descr);
        $descr = trim($descr);
        if(mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$temp_oid' AND temp_host = '$id'"),0) == '0') {
          $query = "INSERT INTO temperature (`temp_host`, `temp_oid`, `temp_descr`) values ('$id', '$temp_oid', '$descr')";
          mysql_query($query);
          echo("+");
        } else { echo("."); }
        $temp_exists[] = "$id $temp_oid";
      }
     }
    }
  }

  ## Papouch TME Temperatures
  if($device['os'] == "papouch-tme") {
    echo("Papouch TME ");
    $descr = trim(shell_exec($config['snmpget'] . " -O qv -$snmpver -c $community $hostname:$port SNMPv2-SMI::enterprises.18248.1.1.3.0"));
    $temp = trim(shell_exec($config['snmpget'] . " -O qv -$snmpver -c $community $hostname:$port SNMPv2-SMI::enterprises.18248.1.1.2.0"));
    if(!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" && $temp != "0") 
    {
      $descr = trim(str_replace("\"", "", $descr));
      if(mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$temp_oid' AND temp_host = '$id'"),0) == '0') 
      {
        $query = "INSERT INTO temperature (`temp_host`, `temp_oid`, `temp_descr`) values ('$id', '$temp_oid', '$descr')";
        mysql_query($query);
        echo("+");
      } else { echo("."); }
      $temp_exists[] = "$id $temp_oid";
    }
  }

  ## Begin Observer-Style
  if($device['os'] == "Linux") {
    echo("Observer-Style ");
    $oids = shell_exec($config['snmpwalk'] . " -$snmpver -m SNMPv2-SMI -Osqn -CI -c $community $hostname:$port .1.3.6.1.4.1.2021.7891 | sed s/.1.3.6.1.4.1.2021.7891.// | grep '.1.1 ' | grep -v '.101.' | cut -d'.' -f 1");
    $oids = trim($oids);
    foreach(explode("\n",$oids) as $oid) {
      $oid = trim($oid);
      if($oid != "") {
        $descr_query = $config['snmpget'] . " -$snmpver -m SNMPv2-SMI -Osqn -c $community $hostname:$port .1.3.6.1.4.1.2021.7891.$oid.2.1 | sed s/.1.3.6.1.4.1.2021.7891.$oid.2.1\ //";
        $descr = trim(str_replace("\"", "", shell_exec($descr_query)));
        $fulloid = ".1.3.6.1.4.1.2021.7891.$oid.101.1";
        if(!mysql_result(mysql_query("SELECT count(temp_id) FROM temperature WHERE `temp_host` = '$id' AND `temp_oid` = '$fulloid'"), 0)) {
          echo("+");
          mysql_query("INSERT INTO `temperature` (`temp_host`,`temp_oid`,`temp_descr`) VALUES ('$id', '$fulloid', '$descr');");
        } elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `temp_host` = '$id' AND `temp_oid` = '$fulloid'"), 0) != $descr) {
          echo("U");
	  mysql_query("UPDATE temperature SET `temp_descr` = '$descr' WHERE `temp_host` = '$id' AND `temp_oid` = '$fulloid'");
        } else {
          echo(".");
        }
        $temp_exists[] = "$id $fulloid";
      }
    }
  } ## End Observer-Style

  ## Dell Temperatures
  if(strstr($device['hardware'], "Dell")) {
    echo("Dell OMSA ");
    $oids = shell_exec($config['snmpwalk'] . " -m MIB-Dell-10892 -$snmpver -CI -Osqn -c $community $hostname:$port .1.3.6.1.4.1.674.10892.1.700.20.1.8");
    $oids = trim($oids);
    foreach(explode("\n",$oids) as $oid) {
      $oid = substr(trim($oid), 36);
      list($oid) = explode(" ", $oid);
      if($oid != "") {
        $descr_query = $config['snmpget'] . " -m MIB-Dell-10892 -$snmpver  -Onvq -c $community $hostname:$port .1.3.6.1.4.1.674.10892.1.700.20.1.8.$oid";
        $descr = trim(str_replace("\"", "", shell_exec($descr_query)));
        $fulloid = ".1.3.6.1.4.1.674.10892.1.700.20.1.6.$oid";
        if(!mysql_result(mysql_query("SELECT count(temp_id) FROM temperature WHERE `temp_host` = '$id' AND `temp_oid` = '$fulloid'"), 0)) {
          mysql_query("INSERT INTO `temperature` (`temp_host`,`temp_oid`,`temp_descr`, `temp_tenths`) VALUES ('$id', '$fulloid', '$descr', '1');");
	  echo("+");
        } elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `temp_host` = '$id' AND `temp_oid` = '$fulloid'"), 0) != $descr) {
          mysql_query("UPDATE temperature SET `temp_descr` = '$descr' WHERE `temp_host` = '$id' AND `temp_oid` = '$fulloid'");
          echo("UPDATE temperature SET `temp_descr` = '$descr' WHERE `temp_host` = '$id' AND `temp_oid` = '$fulloid'");
          echo("U");
        } else {
          echo(".");
        }
        $temp_exists[] = "$id $fulloid";
      }
    } 
  }## End Dell Sensors

  ## Supermicro Temperatures
  if($device['os'] == "linux") {
    $oids = shell_exec($config['snmpwalk'] . " -m SUPERMICRO-HEALTH-MIB -$snmpver -CI -Osqn -c $community $hostname:$port 1.3.6.1.4.1.10876.2.1.1.1.1.3 | sed s/1.3.6.1.4.1.10876.2.1.1.1.1.3.//g");
    $oids = trim($oids);
    if ($oids) echo("Supermicro ");
    foreach(explode("\n", $oids) as $data) {
     $data = trim($data);
     if($data) {
      list($oid,$type) = explode(" ", $data);
      if ($type == 2)
      {
        $temp_oid  = "1.3.6.1.4.1.10876.2.1.1.1.1.4$oid";
        $descr_oid = "1.3.6.1.4.1.10876.2.1.1.1.1.2$oid";
        $descr = shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $descr_oid");
        $temp  = shell_exec($config['snmpget'] . " -m SUPERMICRO-HEALTH-MIB -O qv -$snmpver -c $community $hostname:$port $temp_oid");
        $descr = str_ireplace("temperature", "", $descr);
        $descr = trim($descr);
        if(mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$temp_oid' AND temp_host = '$id'"),0) == '0') {
          $query = "INSERT INTO temperature (`temp_host`, `temp_oid`, `temp_descr`) values ('$id', '$temp_oid', '$descr')";
          mysql_query($query);
          echo("+");
        } else { echo("."); }
        $temp_exists[] = "$id $temp_oid";
      }
     }
    }
  } ## End Supermicro Temperatures

  ## Cisco Temperatures
  if($device['os'] == "ios") {
    echo("Cisco ");
    $oids = shell_exec($config['snmpwalk'] . " -m CISCO-ENVMON-MIB -$snmpver -CI -Osqn -c $community $hostname:$port .1.3.6.1.4.1.9.9.13.1.3.1.2 | sed s/.1.3.6.1.4.1.9.9.13.1.3.1.2.//g");
    $oids = trim($oids);
    foreach(explode("\n", $oids) as $data) {
     $data = trim($data);
     if($data) {
      list($oid) = explode(" ", $data);
      $temp_oid  = ".1.3.6.1.4.1.9.9.13.1.3.1.3.$oid";
      $descr_oid = ".1.3.6.1.4.1.9.9.13.1.3.1.2.$oid";
      $descr = shell_exec($config['snmpget'] . " -m CISCO-ENVMON-MIB -O qv -$snmpver -c $community $hostname:$port $descr_oid");
      $temp  = shell_exec($config['snmpget'] . " -m CISCO-ENVMON-MIB -O qv -$snmpver -c $community $hostname:$port $temp_oid");
      if(!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" ) {
        $descr = str_replace("\"", "", $descr);
        $descr = str_replace("temperature", "", $descr);
        $descr = str_replace("temp", "", $descr);
        $descr = trim($descr);
        if(mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '$temp_oid' AND temp_host = '$id'"),0) == '0') {
          $query = "INSERT INTO temperature (`temp_host`, `temp_oid`, `temp_descr`) values ('$id', '$temp_oid', '$descr')";
          mysql_query($query);
          echo("+");
        } else { echo("."); }
        $temp_exists[] = "$id $temp_oid";
      }
     }
    } 
  } ## End Cisco Temperatures


## Delete removed sensors

$sql = "SELECT * FROM temperature AS T, devices AS D WHERE T.temp_host = D.device_id AND D.device_id = '".$device['device_id']."'";
$query = mysql_query($sql);

while ($sensor = mysql_fetch_array($query)) {
        unset($exists);
        $i = 0;
        while ($i < count($temp_exists) && !$exists) {
            $thistemp = $sensor['temp_host'] . " " . $sensor['temp_oid'];
            if ($temp_exists[$i] == $thistemp) { $exists = 1; }
            $i++;
        }
        if(!$exists) { 
          echo("-");
          mysql_query("DELETE FROM temperature WHERE temp_id = '" . $sensor['temp_id'] . "'"); 
        }
}

unset($temp_exists); echo("\n");


?>
