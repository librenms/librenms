<?
  $id = $device['device_id'];
  $hostname = $device['hostname'];
  $community = $device['community'];
  $snmpver = $device['snmpver'];
  $port = $device['port'];

  echo("Temperatures : ");

  ## JunOS Temperatures
  if($device['os'] == "JunOS") {
    echo("JunOS ");
    $oids = shell_exec($config['snmpwalk'] . " -v2c -CI -Osqn -c $community $hostname:$port 1.3.6.1.4.1.2636.3.1.13.1.7");
    $oids = trim($oids);
    foreach(explode("\n", $oids) as $data) {
     $data = trim($data);
     $data = substr($data, 29);
     if($data) {
      list($oid) = explode(" ", $data);
      $temp_oid  = "1.3.6.1.4.1.2636.3.1.13.1.7.$oid";
      $descr_oid = "1.3.6.1.4.1.2636.3.1.13.1.5.$oid";
      $descr = trim(shell_exec("snmpget -O qv -v2c -c $community $hostname:$port $descr_oid"));
      $temp = trim(shell_exec("snmpget -O qv -v2c -c $community $hostname:$port $temp_oid"));
      if(!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" && $temp != "0") {
        $descr = `snmpget -O qv -v2c -c $community $hostname:$port $descr_oid`;
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

  ## Begin Observer-Style
  if($device['os'] == "Linux") {
    echo("Observer-Style ");
    $oids = `snmpwalk -$snmpver -Osqn -CI -c $community $hostname:$port .1.3.6.1.4.1.2021.7891 | sed s/.1.3.6.1.4.1.2021.7891.// | grep ".1.1 " | grep -v ".101." | cut -d"." -f 1`;
    $oids = trim($oids);
    foreach(explode("\n",$oids) as $oid) {
      $oid = trim($oid);
      if($oid != "") {
        $descr = trim(str_replace("\"", "", `snmpget -v2c -Osqn -c $community $hostname:$port .1.3.6.1.4.1.2021.7891.$oid.2.1 | sed s/.1.3.6.1.4.1.2021.7891.$oid.2.1\ //`));
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
    $oids = shell_exec($config['snmpwalk'] . " -v2c -CI -Osqn -c $community $hostname:$port .1.3.6.1.4.1.674.10892.1.700.20.1.8");
    $oids = trim($oids);
    foreach(explode("\n",$oids) as $oid) {
      $oid = substr(trim($oid), 36);
      list($oid) = explode(" ", $oid);
      if($oid != "") {
        $descr = trim(str_replace("\"", "", `snmpget -v2c -Onvq -c $community $hostname:$port .1.3.6.1.4.1.674.10892.1.700.20.1.8.$oid`));
        $fulloid = ".1.3.6.1.4.1.674.10892.1.700.20.1.6.$oid";
        if(!mysql_result(mysql_query("SELECT count(temp_id) FROM temperature WHERE `temp_host` = '$id' AND `temp_oid` = '$fulloid'"), 0)) {
          mysql_query("INSERT INTO `temperature` (`temp_host`,`temp_oid`,`temp_descr`, `temp_tenths`) VALUES ('$id', '$fulloid', '$descr', '1');");
	  echo("+");
        } elseif (mysql_result(mysql_query("SELECT `temp_descr` FROM temperature WHERE `temp_host` = '$id' AND `temp_oid` = '$fulloid'"), 0) != $descr) {
          mysql_query("UPDATE temperature SET `temp_descr` = '$descr' WHERE `temp_host` = '$id' AND `temp_oid` = '$fulloid'");
          echo("U");
        } else {
          echo(".");
        }
        $temp_exists[] = "$id $fulloid";
      }
    } 
  }## End Dell Sensors


  ## Cisco Temperatures
  if($device['os'] == "IOS" || $device['os'] == "IOS XE") {
    echo("Cisco ");
    $oids = shell_exec($config['snmpwalk'] . " -v2c -CI -Osqn -c $community $hostname:$port .1.3.6.1.4.1.9.9.13.1.3.1.2 | sed s/.1.3.6.1.4.1.9.9.13.1.3.1.2.//g");
    $oids = trim($oids);
    foreach(explode("\n", $oids) as $data) {
     $data = trim($data);
     if($data) {
      list($oid) = explode(" ", $data);
      $temp_oid  = ".1.3.6.1.4.1.9.9.13.1.3.1.3.$oid";
      $descr_oid = ".1.3.6.1.4.1.9.9.13.1.3.1.2.$oid";
      $descr = `snmpget -O qv -v2c -c $community $hostname:$port $descr_oid`;
      $temp = `snmpget -O qv -v2c -c $community $hostname:$port $temp_oid`;
      if(!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" ) {
        $descr = `snmpget -O qv -v2c -c $community $hostname:$port $descr_oid`;
        $descr = str_replace("\"", "", $descr);
        $descr = str_replace("temperature", "", $descr);
        $descr = str_replace("temp", "", $descr);
        $descr = trim($descr);
        if(mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '.1.3.6.1.4.1.9.9.13.1.3.1.3.$oid' AND temp_host = '$id'"),0) == '0') {
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
