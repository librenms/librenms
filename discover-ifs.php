#!/usr/bin/php
<?
include("config.php");
include("includes/functions.php");

# Discover interfaces

$device_query = mysql_query("SELECT device_id,hostname,community,snmpver FROM `devices` WHERE `device_id` LIKE '%" . $argv[1] . "' AND status = '1' AND os != 'Snom' ORDER BY device_id DESC");
while ($device = mysql_fetch_row($device_query)) {

  $id = $device['0'];
  $hostname = $device['1'];
  $community = $device['2'];
  $snmpver = $device['3'];
  $interfaces = `snmpwalk -O nsq -v2c -c $community $hostname ".1.3.6.1.2.1.2.2.1.2" | sed s/ifDescr.//g | sed s/\ \"/\|\|\"/g | sed s/\ /\|\|/g`;
  $interfaces = trim($interfaces);
  echo("Polling $hostname\n");                                                                                                                                                       
  foreach(explode("\n", $interfaces) as $entry){
    $entry = trim($entry);
    list($ifIndex, $ifName) = explode("||", $entry);
    if(!strstr($entry, "irtual")) {
      $ifName = trim(str_replace("\"", "", $ifName));
      $if = trim(strtolower($ifName));
      $nullintf = 0;
      foreach($bif as $bi) {

#        echo("'$bi' -> '$if'\n");

        if (strstr($if, $bi)) {
	  echo("'$bi' -> '$if' MATCH!\n");
          $nullintf = 1;
        }
      }
      if (preg_match('/serial[0-9]:/', $if)) { $nullintf = '1'; }
      if (preg_match('/ng[0-9]+$/', $if)) { $nullintf = '1'; }
      if ($nullintf == 0) {
        if(mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces` WHERE `device_id` = '$id' AND `ifIndex` = '$ifIndex'"), 0) == '0') {
          echo "Adding port $ifName \n";
          mysql_query("INSERT INTO `interfaces` (`device_id`,`ifIndex`,`ifDescr`) VALUES ('$id','$ifIndex','$ifName')");
        } else { 
          # echo("Already have $ifName \n"); 
        }
      } else { 
          # echo("Invalid $ifName\n"); 
      }
    } 
  }
}
?>
