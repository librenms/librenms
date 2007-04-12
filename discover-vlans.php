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

    echo("VLAN Trunking Protocol Version $vtpversion\n");

    $vlans_cmd  = "snmpwalk -O qn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " ";
    $vlans_cmd .= "1.3.6.1.4.1.9.9.46.1.3.1.1.2.1 | sed s/.1.3.6.1.4.1.9.9.46.1.3.1.1.2.1.//g | cut -f 1 -d\" \"";

    $vlans  = trim(`$vlans_cmd | grep -v o`);

    foreach(explode("\n", $vlans) as $vlan) {

      $vlan_descr_cmd  = "snmpget -O nvq -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " "; 
      $vlan_descr_cmd .= ".1.3.6.1.4.1.9.9.46.1.3.1.1.4.1." . $vlan;
      $vlan_descr = `$vlan_descr_cmd`;

      $vlan_descr = trim(str_replace("\"", "", $vlan_descr));

      echo("VLAN $vlan ($vlan_descr)\n");

    }

  }

}
