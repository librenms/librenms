#!/usr/bin/php
<?

include("config.php");
include("includes/functions.php");

$device_query = mysql_query("SELECT * FROM `devices` WHERE `id` like '%$argv[1]' AND `ignore` = '0' ORDER BY `id` ASC");
while ($device = mysql_fetch_array($device_query)) {
  $hostname = $device['hostname'];
  $id = $device['id'];
  $status = $device['status'];
  $community = $device['community'];
  $snmpver = $device['snmpver'];
  unset($update); unset($update_query); unset($seperator);  unset($newversion); unset($newuptime); unset($newfeatures); 
  unset($newlocation); unset($newhardware);
  $pingable = isPingable($hostname);
  $snmpable = FALSE;
  if($pingable) {
    $snmpable = isSNMPable($hostname, $community, $snmpver);
  }
  if ($pingable !== FALSE && $snmpable !== FALSE ) {
    $newstatus = '1';
    $hardware = $device['hardware'];
    $version = $device['version'];
    $old_rebooted = $device['rebooted'];
    $features = $device['features'];
    $location = $device['location'];
    $old_sysDescr = $device['sysDescr'];
    $uptime = $device['uptime'];
    $os = $device['os'];
    if($os == "FreeBSD" || $os == "OpenBSD" || $os == "Linux" || $os == "Windows") { $uptimeoid = ".1.3.6.1.2.1.25.1.1.0"; } else { $uptimeoid = "1.3.6.1.2.1.1.3.0"; }
    if($device['monowall']) { $uptimeoid = ".1.3.6.1.2.1.1.3.0"; }
    $snmp = "$uptimeoid sysLocation.0 .1.3.6.1.2.1.47.1.1.1.1.13.1 sysDescr.0";
    $snmpdata = `snmpget -O qv -$snmpver -c $community $hostname $snmp | grep -v "Cisco Internetwork Operating System Software"`;
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

    include("poll-device-netstats.php");    

    switch ($os) {
    case "FreeBSD":
    case "DragonFly":
    case "OpenBSD":
    case "Linux":
    case "m0n0wall":
    case "Voswall":
    case "NetBSD":
    case "pfSense":
      if($os == "FreeBSD") {
        $sysDescr = str_replace(" 0 ", " ", $sysDescr);
        list(,,$newversion) = explode (" ", $sysDescr);
        $newhardware = "i386";
        $newfeatures = "GENERIC";
      } elseif($os == "DragonFly") {
        list(,,$newversion,,,$newfeatures,,$newhardware) = explode (" ", $sysDescr);
      } elseif($os == "NetBSD") {
        list(,,$newversion,,,$newfeatures) = explode (" ", $sysDescr);
        $newfeatures = str_replace("(", "", $newfeatures);
        $newfeatures = str_replace(")", "", $newfeatures);
        list(,,$newhardware) = explode ("$newfeatures", $sysDescr);
      } elseif($os == "OpenBSD") {
        list(,,$newversion,$newfeatures,$newhardware) = explode (" ", $sysDescr);
        $newfeatures = str_replace("(", "", $newfeatures);
        $newfeatures = str_replace(")", "", $newfeatures);
      } elseif($os == "m0n0wall" || $os == "Voswall") { 
	list(,,$newversion,$newhardware,$freebsda, $freebsdb, $arch) = split(" ", $sysDescr);
	$newfeatures = $freebsda . " " . $freebsdb;
	$newhardware = "$newhardware ($arch)";	
        $newhardware = str_replace("\"", "", $newhardware);
      } elseif ($os == "Linux") {
        list(,,$newversion) = explode (" ", $sysDescr);
        if(strstr($sysDescr, "386")|| strstr($sysDescr, "486")||strstr($sysDescr, "586")||strstr($sysDescr, "686")) { $newhardware = "Generic x86"; }
#        list($newversion,$newfeatures,$newfeaturesb) = explode("-", $newversion);
        $newfeatures = `snmpget -O qv -$snmpver -c $community $hostname .1.3.6.1.4.1.2021.7890.1.101.1`;
        $newfeatures = str_replace("No Such Object available on this agent at this OID", "", $newfeatures);
        $newfeatures = str_replace("\"", "", $newfeatures);
      } 
      pollDeviceUnix();
      break;
    case "Windows":
      if($os == "Windows") {
        if(strstr($sysDescr, "x86")) { $newhardware = "Generic x86"; }
        if(strstr($sysDescr, "Windows Version 5.2")) { $newversion = "2003 Server"; }
        if(strstr($sysDescr, "Uniprocessor Free")) { $newfeatures = "Uniprocessor"; }
        if(strstr($sysDescr, "Multiprocessor Free")) { $newfeatures = "Multiprocessor"; }
      }
      pollDeviceWin();
      break;
    case "IOS":
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
        list($newhardware, $newfeatures, $newversion) = explode("|", $newversion);
        $newfeatures = fixIOSFeatures($newfeatures);
        #$newhardware = fixIOSHardware($newhardware);
        $ciscomodel = str_replace("\"", "", $ciscomodel);
        if(strstr($ciscomodel, "OID")){ unset($ciscomodel); }
        echo("\n|$ciscomodel|$newhardware\n");
        if(!strstr($ciscomodel, " ") && strlen($ciscomodel) >= '3') {
          echo("$ciscomodel");
          $newhardware = $ciscomodel;
        }
        
      }
      pollDeviceIOS();
      break;
    case "ProCurve":
      $sysDescr = str_replace(", ", ",", $sysDescr);
      list($newhardware, $newfeatures, $newversion) = explode(",", $sysDescr);
      list($newversion) = explode("(", $newversion);
      if(!strstr($ciscomodel, " ")) {
        echo("$ciscomodel");
        $newhardware = str_replace("\"", "", $ciscomodel);
      }
      pollDeviceHP();
      break;
    case "Snom":
      $sysDescr = `snmpget -Oqv -$snmpver -c $community $hostname 1.3.6.1.2.1.7526.2.4`;
      $sysDescr = str_replace("-", " ", $sysDescr);
      $sysDescr = str_replace("\"", "", $sysDescr);
      list($newhardware, $newfeatures, $newversion) = explode(" ", $sysDescr);
      pollDeviceSnom();
      break;
    default:
      pollDevice();
    }   
    $newlocation = str_replace("\"","", $sysLocation); 
  } else {
    $newstatus = '0';
  }

  $uptimerrd = "rrd/" . $hostname . "-uptime.rrd";
  if(!is_file($uptimerrd)) {
    $woo = `rrdtool create $uptimerrd \
      DS:uptime:GAUGE:600:0:U \
      RRA:AVERAGE:0.5:1:600 \
      RRA:AVERAGE:0.5:6:700 \
      RRA:AVERAGE:0.5:24:775 \
      RRA:AVERAGE:0.5:288:797`;
  }
  rrd_update($uptimerrd, "N:$newuptime");

  if ( $sysDescr && $sysDescr != $old_sysDescr ) {
    $update = "`sysDescr` = '$sysDescr'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('$id', NULL, NOW(), 'New sysDescr - $sysDescr')");
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

  if ($newuptime) {
    echo("Uptime : $newuptime\n");

    $update_uptime_attrib = mysql_query("UPDATE devices_attribs SET attrib_value = '$newuptime' WHERE `device_id` = '$id' AND `attrib_type` = 'uptime'");
    if(mysql_affected_rows() == '0') {
      $insert_uptime_attrib = mysql_query("INSERT INTO devices_attribs (`device_id`, `attrib_type`, `attrib_value`) VALUES ('$id', 'uptime', '$newuptime')");
    }

    $update_uptime = mysql_query("UPDATE device_uptime SET device_uptime = '$newuptime' WHERE `device_id` = '$id'");
    if(mysql_affected_rows() == '0') {
      $insert_uptime = mysql_query("INSERT INTO device_uptime (`device_uptime`, `device_id`) VALUES ('$newuptime','$id')");
    }
  }


  if ($update) {
    $update_query  = "UPDATE `devices` SET ";
    $update_query .= $update;
    $update_query .= " WHERE `id` = '$id'";
    echo("Updating $hostname\n$update_query\n\n");
    $update_result = mysql_query($update_query);
  } else {
    echo("No Changes to $hostname \n\n");
  }
}   

?>
