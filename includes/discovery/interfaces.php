<?

# Discover interfaces

  echo("Interfaces : ");

  $cmd = $config['snmpwalk'] . " -O nsq -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " .1.3.6.1.2.1.2.2.1.2";
  $interfaces = trim(shell_exec($cmd));
  $interfaces = str_replace("\"", "", $interfaces);
  $interfaces = str_replace("ifDescr.", "", $interfaces);
  $interfaces = str_replace(" ", "||", $interfaces);

  $interface_ignored = 0;
  $interface_added   = 0;

  foreach(explode("\n", $interfaces) as $entry){

    $entry = trim($entry);
    list($ifIndex, $ifName) = explode("||", $entry);
    if(!strstr($entry, "irtual")) {
      $ifName = trim(str_replace("\"", "", $ifName));
      $if = trim(strtolower($ifName));
      $nullintf = 0;
      foreach($config['bad_if'] as $bi) {
      if (strstr($if, $bi)) {
          $nullintf = 1;
        }
      }
      if (preg_match('/serial[0-9]:/', $if)) { $nullintf = '1'; }
      if (preg_match('/ng[0-9]+$/', $if)) { $nullintf = '1'; }
      if ($nullintf == 0) {
        if(mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces` WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) == '0') {
          mysql_query("INSERT INTO `interfaces` (`device_id`,`ifIndex`,`ifDescr`) VALUES ('".$device['device_id']."','$ifIndex','$ifName')");
          # Add Interface
           echo("+");
        } else {
          mysql_query("UPDATE `interfaces` SET `deleted` = '0' WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex' AND `deleted` = '1'"); 
          if(mysql_affected_rows()) { 
            echo("*"); 
          } else {
            echo(".");
          }
        }
        $int_exists[] = "$ifIndex";
      } else { 
          # Ignored Interface
	  echo("$if \n");
          echo("X"); 
      }
    } 
  }


  $sql = "SELECT * FROM `interfaces` WHERE `device_id`  = '".$device['device_id']."' AND `deleted` = '0'";
  $query = mysql_query($sql);

  while ($test_if = mysql_fetch_array($query)) {
        unset($exists);
        $i = 0;
        while ($i < count($int_exists) && !$exists) {
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
