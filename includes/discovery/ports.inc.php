<?php

# Discover ports

echo("Ports : ");

$ports = snmp_walk($device, "ifDescr", "-Onsq", "IF-MIB");

$ports = str_replace("\"", "", $ports);
$ports = str_replace("ifDescr.", "", $ports);
$ports = str_replace(" ", "||", $ports);

$interface_ignored = 0;
$interface_added   = 0;

foreach(explode("\n", $ports) as $entry){

  $entry = trim($entry);
  list($ifIndex, $ifDescr) = explode("||", $entry, 2);
  if(!strstr($entry, "irtual")) {
    $if = trim(strtolower($ifDescr));
    $nullintf = 0;
    foreach($config['bad_if'] as $bi) { if (strstr($if, $bi)) { $nullintf = 1; } }
    if(is_array($config['bad_if_regexp'])) {
      foreach($config['bad_if_regexp'] as $bi) {
	if (preg_match($bi ."i", $if)) {
	  $nullintf = 1;
	}
      }
    }

    if(empty($ifDescr)) { $nullintf = 1; }
    if($device['os'] == "catos" && strstr($if, "vlan") ) { $nullintf = 1; } 
    $ifDescr = fixifName($ifDescr);
    if (preg_match('/serial[0-9]:/', $if)) { $nullintf = 1; }
    if(isset($config['allow_ng']) && !$config['allow_ng']) {
      if (preg_match('/ng[0-9]+$/', $if)) { $nullintf = 1; }
    }
    if ($debug) echo("\n $if ");
    if ($nullintf == 0) {
      if(mysql_result(mysql_query("SELECT COUNT(*) FROM `ports` WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) == '0') {
	mysql_query("INSERT INTO `ports` (`device_id`,`ifIndex`,`ifDescr`) VALUES ('".$device['device_id']."','$ifIndex','$ifDescr')");
        # Add Interface
	echo("+");
      } else {
	mysql_query("UPDATE `ports` SET `deleted` = '0' WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"); 
	echo(".");
      }
      $int_exists[] = "$ifIndex";
    } else { 
      # Ignored Interface
      if(mysql_result(mysql_query("SELECT COUNT(*) FROM `ports` WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) != '0') {
	mysql_query("UPDATE `ports` SET `deleted` = '1' WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'");
        # Delete Interface
	echo("-"); ## Deleted Interface
      } else {
	echo("X"); ## Ignored Interface
      }
    }
  } 
}


$sql = "SELECT * FROM `ports` WHERE `device_id`  = '".$device['device_id']."' AND `deleted` = '0'";
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
    mysql_query("UPDATE `ports` SET `deleted` = '1' WHERE interface_id = '" . $test_if['interface_id'] . "'");
  }
}

unset($temp_exists);
echo("\n");

?>
