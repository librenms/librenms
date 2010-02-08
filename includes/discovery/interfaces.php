<?php

# Discover interfaces

  echo("Interfaces : ");

  $interfaces = snmp_walk($device['hostname'], $device['port'], $device['snmpver'], $device['community'], "ifDescr", "Onsq", "IF-MIB");

  $interfaces = str_replace("\"", "", $interfaces);
  $interfaces = str_replace("ifDescr.", "", $interfaces);
  $interfaces = str_replace(" ", "||", $interfaces);

  $interface_ignored = 0;
  $interface_added   = 0;

  foreach(explode("\n", $interfaces) as $entry){

    $entry = trim($entry);
    list($ifIndex, $ifDescr) = explode("||", $entry);

    if(!strstr($entry, "irtual")) {
      $if = trim(strtolower($ifDescr));
      $nullintf = 0;
      foreach($config['bad_if'] as $bi) { if (strstr($if, $bi)) { $nullintf = 1; } }
      if($device['os'] == "catos" && strstr($if, "vlan") ) { $nullintf = 1; } 
      $ifDescr = fixifName($ifDescr);
      if (preg_match('/serial[0-9]:/', $if)) { $nullintf = 1; }
      if(isset($config['allow_ng']) && !$config['allow_ng']) {
       if (preg_match('/ng[0-9]+$/', $if)) { $nullintf = 1; }
      }
      if ($debug) echo("\n $if ");
      if ($nullintf == 0) {
        if(mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces` WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) == '0') {
          mysql_query("INSERT INTO `interfaces` (`device_id`,`ifIndex`,`ifDescr`) VALUES ('".$device['device_id']."','$ifIndex','$ifDescr')");
          # Add Interface
           echo("+");
        } else {
          if(isset($interface['deleted']) && $interface['deleted']) {
            mysql_query("UPDATE `interfaces` SET `deleted` = '0' WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"); 
            echo("*"); 
          } else {
            echo(".");
          }
        }
        $int_exists[] = "$ifIndex";
      } else { 
        # Ignored Interface
	if(mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces` WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) != '0') {
          mysql_query("UPDATE `interfaces` SET `deleted` = '1' WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'");
          # Delete Interface
          echo("-"); ## Deleted Interface
        } else {
          echo("X"); ## Ignored Interface
        }
      }
    } 
  }


  $sql = "SELECT * FROM `interfaces` WHERE `device_id`  = '".$device['device_id']."' AND `deleted` = '0'";
  $query = mysql_query($sql);

  while ($test_if = mysql_fetch_array($query)) {
        unset($exists);
        $i = 0;
        while ($i < count($int_exists) && !isset($exists)) {
            $this_if = $test_if['ifIndex'];
            if ($int_exists[$i] == $this_if) { $exists = 1; }
            $i++;
        }
        if(!$exists) {
          echo("-");
          mysql_query("UPDATE `interfaces` SET `deleted` = '1' WHERE interface_id = '" . $test_if['interface_id'] . "'");
        }
  }

  unset($temp_exists);
  echo("\n");

?>
