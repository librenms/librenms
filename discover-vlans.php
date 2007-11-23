#!/usr/bin/php
<?

include("config.php");
include("includes/functions.php");

$device_query = mysql_query("SELECT * FROM `devices` WHERE status = '1' AND os = 'IOS'");
while ($device = mysql_fetch_array($device_query)) {

  echo("Discovering VLANs on " . $device['hostname'] . "\n");

  $vtpversion_cmd = "snmpget -Oqv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " .1.3.6.1.4.1.9.9.46.1.1.1.0";
  $vtpversion = trim(`$vtpversion_cmd 2>/dev/null`);  

  if($vtpversion == '1' || $vtpversion == '2') { 

    $vtp_domain_cmd = "snmpget -Oqv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " .1.3.6.1.4.1.9.9.46.1.2.1.1.2.1";
    $vtp_domain = trim(str_replace("\"", "", `$vtp_domain_cmd 2>/dev/null`));

    echo("VLAN Trunking Protocol Version $vtpversion Domain : $vtp_domain\n");

    $vlans_cmd  = "snmpwalk -O qn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " ";
    $vlans_cmd .= "1.3.6.1.4.1.9.9.46.1.3.1.1.2.1 | sed s/.1.3.6.1.4.1.9.9.46.1.3.1.1.2.1.//g | cut -f 1 -d\" \"";

    $vlans  = trim(`$vlans_cmd | grep -v o`);

    foreach(explode("\n", $vlans) as $vlan) {

      $vlan_descr_cmd  = "snmpget -O nvq -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " "; 
      $vlan_descr_cmd .= ".1.3.6.1.4.1.9.9.46.1.3.1.1.4.1." . $vlan;
      $vlan_descr = `$vlan_descr_cmd`;

      $vlan_descr = trim(str_replace("\"", "", $vlan_descr));

      if(mysql_result(mysql_query("SELECT COUNT(vlan_id) FROM `vlans` WHERE `device_id` = '" . $device['device_id'] . "' AND `vlan_domain` = '" . $vtp_domain . "' AND `vlan_vlan` = '" . $vlan . "'"), 0) == '0') {
        echo "Adding VLAN $vlan - $vlan_descr \n";
        mysql_query("INSERT INTO `vlans` (`device_id`,`vlan_domain`,`vlan_vlan`, `vlan_descr`) VALUES (" . $device['device_id'] . ",'" . $vtp_domain . "','$vlan', '$vlan_descr')");
      }

      echo("VLAN $vlan ($vlan_descr)\n");

      $this_vlans[] = $vlan;

    }

    $device_vlans = mysql_query("SELECT * FROM `vlans` WHERE `device_id` = '" . $device['device_id'] . "' AND `vlan_domain` = '" . $vtp_domain . "'");
    while($dev_vlan = mysql_fetch_array($device_vlans)) {
      unset($vlan_exists);
      foreach($this_vlans as $test_vlan) {
        if($test_vlan == $dev_vlan['vlan_vlan']) { $vlan_exists = 1; }
      }
      if(!$vlan_exists) { mysql_query("DELETE FROM `vlans` WHERE `vlan_id` = '" . $dev_vlan['vlan_id'] . "'"); echo("Deleted VLAN ". $dev_vlan['vlan_vlan'] ."\n"); }
    }
  }
}
