#!/usr/bin/php
<?
include("config.php");
include("includes/functions.php");


### Discovery Observer-style temperature sensors

$device_query = mysql_query("SELECT * FROM `devices` WHERE status = '1' AND os != 'IOS' AND os != 'ProCurve'");
while ($device = mysql_fetch_array($device_query)) {
  $id = $device['id'];
  $hostname = $device['hostname'];
  $community = $device['community'];
  $snmpver = $device['snmpver'];
  echo("\n***$hostname***\n");
  $oids = `snmpwalk -$snmpver -Osqn -c $community $hostname .1.3.6.1.4.1.2021.7891 | sed s/.1.3.6.1.4.1.2021.7891.// | grep ".1.1 " | grep -v ".101." | cut -d"." -f 1`;
  #$oids = `snmpwalk -v2c -Osqn -c $community $hostname .1.3.6.1.4.1.2021.7891 | sed s/.1.3.6.1.4.1.2021.7891.// | grep '1.1.1' | grep -v 0 | cut -d " " -f 2`;
  $oids = trim($oids);
  if(strstr($oids, "no")) { unset ($oids); }
  foreach(explode("\n",$oids) as $oid) {
   $oid = trim($oid);
   if($oid != "") {
    $descr = trim(str_replace("\"", "", `snmpget -v2c -Osqn -c $community $hostname .1.3.6.1.4.1.2021.7891.$oid.2.1 | sed s/.1.3.6.1.4.1.2021.7891.$oid.2.1\ //`));
    $fulloid = ".1.3.6.1.4.1.2021.7891.$oid.101.1";
    echo("Detected : $fulloid ($descr)\n");
    if(!mysql_result(mysql_query("SELECT count(temp_id) FROM temperature WHERE `temp_host` = '$id' AND `temp_oid` = '$fulloid'"), 0)) {
      mysql_query("INSERT INTO `temperature` (`temp_host`,`temp_oid`,`temp_descr`) VALUES ('$id', '$fulloid', '$descr');");
      echo("Created $fulloid on $hostname\n");
    }
   }
  }
}
?>

