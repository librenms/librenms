#!/usr/bin/php
<?
include("config.php");
include("includes/functions.php");


### Discovery Observer-style NetSNMP temperatures

$device_query = mysql_query("SELECT * FROM `devices` WHERE status = '1' AND os != 'IOS' AND os != 'ProCurve'");
while ($device = mysql_fetch_array($device_query)) {
  $id = $device['device_id'];
  $hostname = $device['hostname'];
  $community = $device['community'];
  $snmpver = $device['snmpver'];
  echo("\n***$hostname***\n");
  $oids = `snmpwalk -$snmpver -Osqn -c $community $hostname .1.3.6.1.4.1.2021.7891 | sed s/.1.3.6.1.4.1.2021.7891.// | grep ".1.1 " | grep -v ".101." | cut -d"." -f 1`;
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
    } else { $temp_exists[] = "$id $fulloid"; }
   }
  }
}

// Discover Dell temperatures

$device_query = mysql_query("SELECT * FROM `devices` WHERE status = '1' AND hardware like 'Dell %'");
while ($device = mysql_fetch_array($device_query)) {
  $id = $device['device_id'];
  $hostname = $device['hostname'];
  $community = $device['community'];
  $snmpver = $device['snmpver'];
  echo("\n***$hostname***\n");
  $oids = `snmpwalk -$snmpver -Osqn -c $community $hostname .1.3.6.1.4.1.674.10892.1.700.20.1.8`;
  $oids = trim($oids);
  if(strstr($oids, "no")) { unset ($oids); }
  foreach(explode("\n",$oids) as $oid) {
   $oid = substr(trim($oid), 36);  
   echo("$oid \n");
   list($oid) = explode(" ", $oid);
   if($oid != "") {
    $descr = trim(str_replace("\"", "", `snmpget -v2c -Onvq -c $community $hostname .1.3.6.1.4.1.674.10892.1.700.20.1.8.$oid`));
    $fulloid = ".1.3.6.1.4.1.674.10892.1.700.20.1.6.$oid";
    echo("Detected : $fulloid ($descr)\n");
    if(!mysql_result(mysql_query("SELECT count(temp_id) FROM temperature WHERE `temp_host` = '$id' AND `temp_oid` = '$fulloid'"), 0)) {
      mysql_query("INSERT INTO `temperature` (`temp_host`,`temp_oid`,`temp_descr`, `temp_tenths`) VALUES ('$id', '$fulloid', '$descr', '1');");
      echo("Created $fulloid on $hostname\n");
    }
    $temp_exists[] = "$id $fulloid";
   }
  }
}

$sql = "SELECT * FROM temperature";
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
          echo("Deleting...\n");
          mysql_query("DELETE FROM temperature WHERE temp_id = '" . $sensor['temp_id'] . "'"); 
        }


}


?>

