#!/usr/bin/php
<?

include("config.php");
include("includes/functions.php");

$device_query = mysql_query("SELECT * FROM `devices` WHERE id = '$argv[1]'");
while ($device = mysql_fetch_array($device_query)) {

  $hostname = $device['hostname'];
  $id = $device['id'];
  $status = $device['status'];
  unset($update);
  unset($update_query);
  unset($seperator);
  unset($newversion);
  unset($newuptime);
  unset($newfeatures);
  unset($newlocation);
  unset($newhardware);
  $pingable = isPingable($hostname);
  $snmpable = FALSE;
  if($pingable) {
    $snmpable = isSNMPable($hostname);
  }
  echo("$snmpable");
  if ($pingable !== FALSE && $snmpable !== FALSE ) {
    $newstatus = '1';
    $hardware = $device['hardware'];
    $version = $device['version'];
    $old_rebooted = $device['rebooted'];
    $features = $device['features'];
    $location = $device['location'];
    $uptime = $device['uptime'];
    $os = $device['os'];
    if($os == "FreeBSD" || $os == "Linux" || $os == "Windows") { $uptimeoid = ".1.3.6.1.2.1.25.1.1.0"; } else { $uptimeoid = "1.3.6.1.2.1.1.3.0"; }
    $snmp = "$uptimeoid sysLocation.0 .1.3.6.1.2.1.47.1.1.1.1.13.1 sysDescr.0";
    $snmpdata = `snmpget -O qv -v2c -c $community $hostname $snmp | grep -v "Cisco Internetwork Operating System Software"`;
    $snmpdata = preg_replace("/^.*IOS/","", $snmpdata);
    $snmpdata = trim($snmpdata);
    list($sysUptime, $sysLocation, $ciscomodel, $sysDescr) = explode("\n", $snmpdata);
    $sysUptime = str_replace("(", "", $sysUptime);
    $sysUptime = str_replace(")", "", $sysUptime); 
    list($days, $hours, $mins, $secs) = explode(":", $sysUptime);
    list($secs, $microsecs) = explode(".", $secs);
    $timeticks =  mktime(0, $secs, $mins, $hours, $days, 0);
    $hours = $hours + ($days * 24);
    $mins = $mins + ($hours * 60);
    $secs = $secs + ($mins * 60);
    $newuptime = $secs;
    if($os == "Windows") {
	if(strstr($sysDescr, "x86")) { $newhardware = "x86 PC"; }
	if(strstr($sysDescr, "Windows Version 5.2")) { $newversion = "2003 Server"; }
	if(strstr($sysDescr, "Uniprocessor Free")) { $newfeatures = "Uniprocessor"; }
        if(strstr($sysDescr, "Multiprocessor Free")) { $newfeatures = "Multiprocessor"; }
    }
    if($os == "FreeBSD") {
      $sysDescr = str_replace(" 0 ", " ", $sysDescr);
      list(,,$newversion) = explode (" ", $sysDescr);
      $newhardware = "Generic";
      list($newversion,$newfeatures) = explode("-", $newversion);
    }
    if($os == "Linux") {
      list(,,$newversion) = explode (" ", $sysDescr); 
      if(strstr($sysDescr, "386")|| strstr($sysDescr, "486")||strstr($sysDescr, "586")||strstr($sysDescr, "686")) { $newhardware = "Generic x86"; }
      list($newversion,$newfeatures,$newfeaturesb) = explode("-", $newversion);
    }
    if ($os == "IOS") {
      $newversion = str_replace("Cisco IOS Software,", "", $sysDescr);
      $newversion = str_replace("IOS (tm) ", "", $newversion);
      $newversion = str_replace(",RELEASE SOFTWARE", "", $newversion);
      $newversion = str_replace(",MAINTENANCE INTERIM SOFTWARE", "", $newversion);
      $newversion = str_replace("Version ","", $newversion);
      $newversion = str_replace("Cisco Internetwork Operating System Software", "", $newversion);
      $newversion = trim($newversion);
      list($newversion) = explode("\n", $newversion);
      $newversion = preg_replace("/^[A-Za-z0-9\ \_]*\(([A-Za-z0-9\-\_]*)\), (.+), .*/", "\\1|\\2", $newversion);
      $newversion = str_replace("-M|", "|", $newversion);
      $newversion = str_replace("-", "|", $newversion);
      echo("$newversion");
      list($newhardware, $newfeatures, $newversion) = explode("|", $newversion);;
      $newfeatures = fixIOSFeatures($newfeatures);
      $newhardware = fixIOSHardware($newhardware);
      if(!strstr($ciscomodel, " ")) {
        echo("$ciscomodel");
        $newhardware = str_replacE("\"", "", $ciscomodel);
      }
    }
    $newlocation = str_replace("\"","", $sysLocation); 

  } else {
    $newstatus = '0';
  }

  if ( $newlocation && $location != $newlocation ) {
     $update = "`location` = '$newlocation'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('$id', NULL, NOW(), 'Changed location from $location to $newlocation')");
  }
  if ( $newversion && $version != $newversion ) {
     $update .= $seperator . "`version` = '$newversion'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('$id', NULL, NOW(), 'Changed version from $version to $newversion')");
  }
  if ( $newfeatures && $newfeatures != $features ) {
     $update .= $seperator . "`features` = '$newfeatures'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('$id', NULL, NOW(), 'Changed features from $features to $newfeatures')");
  }

  if ( $newhardware && $newhardware != $hardware ) {
     $update .= $seperator . "`hardware` = '$newhardware'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('$id', NULL, NOW(), 'Changed hardware from $hardware to $newhardware')");
  }

  if ( $newuptime && $uptime != $newuptime ) {
     $update .= $seperator . "`uptime` = '$newuptime'";
     $seperator = ", ";
  }
  if ( $newuptime && $newuptime < $uptime  ) {
     $update .= $seperator . "`lastchange` = NOW()";
     $seperator = ", ";
  } elseif($status != $newstatus) { 
     $update .= $seperator . "`lastchange` = NOW()";
     $seperator = ", ";
  }
  if( $status != $newstatus ) {
     $update .= $seperator . "`status` = '$newstatus'";
     $seperator = ", ";
     if ($newstatus == '1') { $stat = "Up"; 
       mysql_query("INSERT INTO alerts (importance, device_id, message) VALUES ('0', '$id', 'Device is up\n')");
     } else { 
       $stat = "Down"; 
       mysql_query("INSERT INTO alerts (importance, device_id, message) VALUES ('9', '$id', 'Device is down\n')");
     }
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('$id', NULL, NOW(), 'Device status changed to $stat')");
  }
  if ($update) {
     $update_query  = "UPDATE `devices` SET ";
     $update_query .= $update;
     $update_query .= " WHERE `id` = '$id'";
     echo("Updating : $hostname\n$update_query\n\n");
     $update_result = mysql_query($update_query);
  } else {
     echo("Not Updating : $hostname \n\n");
  }
}   

?>
