#!/usr/bin/php
<?

include("config.php");
include("includes/functions.php");

$device_query = mysql_query("SELECT * FROM `devices` WHERE `device_id` like '%$argv[1]' AND `ignore` = '0' ORDER BY `device_id` ASC");
while ($device = mysql_fetch_array($device_query)) {

  unset($update); unset($update_query); unset($seperator);  unset($newversion); unset($newuptime); unset($newfeatures); 
  unset($newlocation); unset($newhardware);
  $pingable = isPingable($device['hostname']);
  $snmpable = FALSE;

  if($pingable) {
    $snmpable = isSNMPable($device['hostname'], $device['community'], $device['snmpver']);
  }

  if ($pingable !== FALSE && $snmpable !== FALSE ) {
    $status = '1';
    if($device['os'] == "FreeBSD" || $device['os'] == "OpenBSD" || $device['os'] == "Linux" || $device['os'] == "Windows") { 
      $uptimeoid = ".1.3.6.1.2.1.25.1.1.0"; 
    } else { 
      $uptimeoid = "1.3.6.1.2.1.1.3.0"; 
    }
    $snmp_cmd =  "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .  $device['hostname'];
    $snmp_cmd .= " $uptimeoid sysLocation.0 .1.3.6.1.2.1.47.1.1.1.1.13.1 sysDescr.0";
    $snmp_cmd .= " | grep -v 'Cisco Internetwork Operating System Software'";
    $snmpdata = `$snmp_cmd`;
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

    switch ($device['os']) {
    case "FreeBSD":
    case "DragonFly":
    case "OpenBSD":
    case "Linux":
    case "m0n0wall":
    case "Voswall":
    case "NetBSD":
    case "pfSense":
      if($device['os'] == "FreeBSD") {
        $sysDescr = str_replace(" 0 ", " ", $sysDescr);
        list(,,$newversion) = explode (" ", $sysDescr);
        $newhardware = "i386";
        $newfeatures = "GENERIC";
      } elseif($device['os'] == "DragonFly") {
        list(,,$newversion,,,$newfeatures,,$newhardware) = explode (" ", $sysDescr);
      } elseif($device['os'] == "NetBSD") {
        list(,,$newversion,,,$newfeatures) = explode (" ", $sysDescr);
        $newfeatures = str_replace("(", "", $newfeatures);
        $newfeatures = str_replace(")", "", $newfeatures);
        list(,,$newhardware) = explode ("$newfeatures", $sysDescr);
      } elseif($device['os'] == "OpenBSD") {
        list(,,$newversion,$newfeatures,$newhardware) = explode (" ", $sysDescr);
        $newfeatures = str_replace("(", "", $newfeatures);
        $newfeatures = str_replace(")", "", $newfeatures);
      } elseif($device['os'] == "m0n0wall" || $device['os'] == "Voswall") { 
	list(,,$newversion,$newhardware,$freebsda, $freebsdb, $arch) = split(" ", $sysDescr);
	$newfeatures = $freebsda . " " . $freebsdb;
	$newhardware = "$newhardware ($arch)";	
        $newhardware = str_replace("\"", "", $newhardware);
      } elseif ($device['os'] == "Linux") {
        list(,,$newversion) = explode (" ", $sysDescr);
        if(strstr($sysDescr, "386")|| strstr($sysDescr, "486")||strstr($sysDescr, "586")||strstr($sysDescr, "686")) { $newhardware = "Generic x86"; }
#        list($newversion,$newfeatures,$newfeaturesb) = explode("-", $newversion);
        $cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " .1.3.6.1.4.1.2021.7890.1.101.1";
        $newfeatures = `$cmd`;
        $newfeatures = str_replace("No Such Object available on this agent at this OID", "", $newfeatures);
        $newfeatures = str_replace("\"", "", $newfeatures);
      } 
      include("includes/polling/device-unix.inc.php");
      break;
    case "Windows":
      if($device['os'] == "Windows") {
        if(strstr($sysDescr, "x86")) { $newhardware = "Generic x86"; }
        if(strstr($sysDescr, "Windows Version 5.2")) { $newversion = "2003 Server"; }
        if(strstr($sysDescr, "Uniprocessor Free")) { $newfeatures = "Uniprocessor"; }
        if(strstr($sysDescr, "Multiprocessor Free")) { $newfeatures = "Multiprocessor"; }
      }
      pollDeviceWin();
      break;
    case "IOS":
      if ($device['os'] == "IOS") {
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
      $cmd      = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " 1.3.6.1.2.1.7526.2.4";
      $sysDescr = `$cmd`;
      $sysDescr = str_replace("-", " ", $sysDescr);
      $sysDescr = str_replace("\"", "", $sysDescr);
      list($newhardware, $newfeatures, $newversion) = explode(" ", $sysDescr);
      pollDeviceSnom();
      break;
    default:
      pollDevice();
    }   
    $newlocation = str_replace("\"","", $sysLocation); 
  
  include("includes/polling/temperatures.inc.php");
  include("includes/polling/device-netstats.inc.php");

  } else {
    $newstatus = '0';
  }

  if ( $sysDescr && $sysDescr != $device['sysDescr'] ) {
    $update = "`sysDescr` = '$sysDescr'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'sysDescr -> $sysDescr')");
  }
  if ( $newlocation && $device['location'] != $newlocation ) {
    $update = "`location` = '$newlocation'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'Location -> $newlocation')");
  }
  if ( $newversion && $device['version'] != $newversion ) {
    $update .= $seperator . "`version` = '$newversion'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'OS Version -> $newversion')");
  }
  if ( $newfeatures && $newfeatures != $device['features'] ) {
    $update .= $seperator . "`features` = '$newfeatures'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'OS Features -> $newfeatures')");
  }
  if ( $newhardware && $newhardware != $device['hardware'] ) {
    $update .= $seperator . "`hardware` = '$newhardware'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'Hardware -> $newhardware')");
  }

  if( $device['status'] != $newstatus ) {
    $update .= $seperator . "`status` = '$newstatus'";
    $seperator = ", ";
    if ($newstatus == '1') { $stat = "Up"; 
      mysql_query("INSERT INTO alerts (importance, device_id, message) VALUES ('0', '" . $device['device_id'] . "', 'Device is up\n')");
    } else { 
      $stat = "Down"; 
      mysql_query("INSERT INTO alerts (importance, device_id, message) VALUES ('9', '" . $device['device_id'] . "', 'Device is down\n')");
    }
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'Device status changed to $stat')");
  }

  if ($newuptime) {
    echo("Uptime : $newuptime\n");

    $uptimerrd = "rrd/" . $device['hostname'] . "-uptime.rrd";
    if(!is_file($uptimerrd)) {
      $woo = `rrdtool create $uptimerrd \
        DS:uptime:GAUGE:600:0:U \
        RRA:AVERAGE:0.5:1:600 \
        RRA:AVERAGE:0.5:6:700 \
        RRA:AVERAGE:0.5:24:775 \
        RRA:AVERAGE:0.5:288:797`;
    }
    rrd_update($uptimerrd, "N:$newuptime");

    $update_uptime_attrib = mysql_query("UPDATE devices_attribs SET attrib_value = '$newuptime' WHERE `device_id` = '" . $device['device_id'] . "' AND `attrib_type` = 'uptime'");
    if(mysql_affected_rows() == '0') {
      $insert_uptime_attrib = mysql_query("INSERT INTO devices_attribs (`device_id`, `attrib_type`, `attrib_value`) VALUES ('" . $device['device_id'] . "', 'uptime', '$newuptime')");
    }
  }


  if ($update) {
    $update_query  = "UPDATE `devices` SET ";
    $update_query .= $update;
    $update_query .= " WHERE `id` = '" . $device['device_id'] . "'";
    echo("Updating " . $device['hostname'] . "\n" . $update_query . "\n\n");
    $update_result = mysql_query($update_query);
  } else {
    echo("No Changes to " . $device['hostname'] . "\n\n");
  }
}   

?>
