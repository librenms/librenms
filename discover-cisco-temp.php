#!/usr/bin/php
<?
include("config.php");
include("includes/functions.php");

$device_query = mysql_query("SELECT * FROM `devices` WHERE `os` = 'IOS' AND `status` = '1'");
while ($device = mysql_fetch_array($device_query)) {
  $id = $device['id'];
  $hostname = $device['hostname'];
  $community = $device['community'];
  echo("Detecting IOS temperature sensors for $hostname\n");
  $oids = `snmpwalk -v2c -Osqn -c $community $hostname .1.3.6.1.4.1.9.9.13.1.3.1.2 | sed s/.1.3.6.1.4.1.9.9.13.1.3.1.2.//g`;
  $oids = trim($oids);
  foreach(explode("\n", $oids) as $data) {
   $data = trim($data);
   list($oid) = explode(" ", $data);
   $temp_oid  = ".1.3.6.1.4.1.9.9.13.1.3.1.3.$oid";
   $descr_oid = ".1.3.6.1.4.1.9.9.13.1.3.1.2.$oid";
   $descr = `snmpget -O qv -v2c -c $community $hostname $descr_oid`;
   $temp = `snmpget -O qv -v2c -c $community $hostname $temp_oid`;
   if(!strstr($descr, "No") && !strstr($temp, "No") && $descr != "" ) {
    $descr = `snmpget -O qv -v2c -c $community $hostname $descr_oid`;
    $descr = str_replace("\"", "", $descr);
    $descr = str_replace("temperature", "", $descr);
    $descr = str_replace("temp", "", $descr);
    $descr = trim($descr);
    if(mysql_result(mysql_query("SELECT count(temp_id) FROM `temperature` WHERE temp_oid = '.1.3.6.1.4.1.9.9.13.1.3.1.3.$oid' AND temp_host = '$id'"),0) == '0') {
      $query = "INSERT INTO temperature (`temp_host`, `temp_oid`, `temp_descr`) values ('$id', '$temp_oid', '$descr')";
      echo("$query -> $descr : $temp\n");
      mysql_query($query);
    }
   }
  }
}
?>
