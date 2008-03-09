#!/usr/bin/php
<?

include("config.php");
include("includes/functions.php");

echo("Project Observer Poller v$observer_version\n\n");

if($argv[1] == "--device" && $argv[2]) {  
  $where = "AND `device_id` = '".$argv[2]."'";
} elseif ($argv[1] == "--odd") {
  $where = "AND MOD(device_id,2) = 1";
} elseif ($argv[1] == "--even") {
  $where = "AND MOD(device_id,2) = 0";
} elseif ($argv[1] == "--all") {
  $where = "";
} else {
  echo("--device <device id>    Poll single device\n");
  echo("--all                   Poll all devices\n\n");
  echo("No polling type specified!\n");
  exit;
}

echo("Starting polling run:\n\n");



$device_query = mysql_query("SELECT * FROM `devices` WHERE `ignore` = '0' $where  ORDER BY `device_id` ASC");
while ($device = mysql_fetch_array($device_query)) {

  echo("Polling " . $device['hostname'] . " ( ".$device['device_id']." )\n\n");

  unset($update); unset($update_query); unset($seperator); unset($version); unset($uptime); unset($features); 
  unset($location); unset($hardware); unset($sysDescr);

  $pingable = isPingable($device['hostname']);

  if($pingable) { echo("Pings : yes :)\n"); } else { echo("Pings : no :(\n"); }

  $snmpable = FALSE;

  if($pingable) {
    $snmpable = isSNMPable($device['hostname'], $device['community'], $device['snmpver']);
    if($snmpable) { echo("SNMP : yes :)"); } else { echo("SNMP : no :("); }
  }

  echo("\n");

  if ($snmpable) {
    $status = '1';
    if($device['os'] == "FreeBSD" || $device['os'] == "OpenBSD" || $device['os'] == "Linux" || $device['os'] == "Windows") { 
      $uptimeoid = ".1.3.6.1.2.1.25.1.1.0"; 
    } else { 
      $uptimeoid = "1.3.6.1.2.1.1.3.0"; 
    }
      $snmp_cmd =  "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .  $device['hostname'];
      $snmp_cmd .= " $uptimeoid sysLocation.0 sysContact.0 sysDescr.0";
      $snmp_cmd .= " | grep -v 'Cisco Internetwork Operating System Software'";
    if($device['os'] == "IOS") { 
      $snmp_cmdb =  "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .  $device['hostname'];
      $snmp_cmdb .= " .1.3.6.1.2.1.47.1.1.1.1.13.1";
      $snmp_cmdb .= " | grep -v 'Cisco Internetwork Operating System Software'";
      $ciscomodel = str_replace("\"", "", trim(`$snmp_cmdb`));

    } else { unset($ciscomodel); }

    $snmpdata = `$snmp_cmd`;
    $snmpdata = preg_replace("/^.*IOS/","", $snmpdata);
    $snmpdata = trim($snmpdata);
    $snmpdata = str_replace("\"", "", $snmpdata);
    list($sysUptime, $sysLocation, $sysContact, $sysDescr) = explode("\n", $snmpdata);
    $sysUptime = str_replace("(", "", $sysUptime);
    $sysUptime = str_replace(")", "", $sysUptime); 
    list($days, $hours, $mins, $secs) = explode(":", $sysUptime);
    list($secs, $microsecs) = explode(".", $secs);
    $timeticks =  mktime(0, $secs, $mins, $hours, $days, 0);
    $hours = $hours + ($days * 24);
    $mins = $mins + ($hours * 60);
    $secs = $secs + ($mins * 60);
    $uptime = $secs;

    switch ($device['os']) {
    case "FreeBSD":
    case "DragonFly":
    case "OpenBSD":
    case "Linux":
    case "m0n0wall":
    case "Voswall":
    case "NetBSD":
    case "pfSense":
      if ($device['os'] == "FreeBSD") {
        $sysDescr = str_replace(" 0 ", " ", $sysDescr);
        list(,,$version) = explode (" ", $sysDescr);
        $hardware = "i386";
        $features = "GENERIC";
      } elseif ($device['os'] == "DragonFly") {
        list(,,$version,,,$features,,$hardware) = explode (" ", $sysDescr);
      } elseif ($device['os'] == "NetBSD") {
        list(,,$version,,,$features) = explode (" ", $sysDescr);
        $features = str_replace("(", "", $features);
        $features = str_replace(")", "", $features);
        list(,,$hardware) = explode ("$features", $sysDescr);
      } elseif ($device['os'] == "OpenBSD") {
        list(,,$version,$features,$hardware) = explode (" ", $sysDescr);
        $features = str_replace("(", "", $features);
        $features = str_replace(")", "", $features);
      } elseif ($device['os'] == "m0n0wall" || $device['os'] == "Voswall") { 
	list(,,$version,$hardware,$freebsda, $freebsdb, $arch) = split(" ", $sysDescr);
	$features = $freebsda . " " . $freebsdb;
	$hardware = "$hardware ($arch)";	
        $hardware = str_replace("\"", "", $hardware);
      } elseif ($device['os'] == "Linux") {
        list(,,$version) = explode (" ", $sysDescr);
        if(strstr($sysDescr, "386")|| strstr($sysDescr, "486")||strstr($sysDescr, "586")||strstr($sysDescr, "686")) { $hardware = "Generic x86"; }
        if(strstr($sysDescr, "x86_64")) { $hardware = "Generic x86_64"; }
        $cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " .1.3.6.1.4.1.2021.7890.1.101.1";
        $features = trim(`$cmd`);
        $features = str_replace("No Such Object available on this agent at this OID", "", $features);
        $features = str_replace("\"", "", $features);
        // Detect Dell hardware via OpenManage SNMP
        $cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " .1.3.6.1.4.1.674.10892.1.300.10.1.9.1";
        $hw = trim(str_replace("\"", "", `$cmd`));      
        if(strstr($hw, "No")) { unset($hw); } else { $hardware = "Dell " . $hw; }
      }

      include("includes/polling/device-unix.inc.php");
      break;
    case "Windows":
      if(strstr($sysDescr, "x86")) { $hardware = "Generic x86"; }
      if(strstr($sysDescr, "Windows Version 5.2")) { $version = "2003 Server"; }
      if(strstr($sysDescr, "Uniprocessor Free")) { $features = "Uniprocessor"; }
      if(strstr($sysDescr, "Multiprocessor Free")) { $features = "Multiprocessor"; }
      pollDeviceWin();
      break;

    case "IOS":
      echo("Device is Cisco! \n$sysDescr\n");
      $version = str_replace("Cisco IOS Software,", "", $sysDescr);
      $version = str_replace("IOS (tm) ", "", $version);
      $version = str_replace(",RELEASE SOFTWARE", "", $version);
      $version = str_replace(",MAINTENANCE INTERIM SOFTWARE", "", $version);
      $version = str_replace("Version ","", $version);
      $version = str_replace("Cisco Internetwork Operating System Software", "", $version);
      $version = trim($version);
      list($version) = explode("\n", $version);
      $version = preg_replace("/^[A-Za-z0-9\ \_]*\(([A-Za-z0-9\-\_]*)\), (.+), .*/", "\\1|\\2", $version);
      $version = str_replace("-M|", "|", $version);
      $version = str_replace("-", "|", $version);
      list($hardware, $features, $version) = explode("|", $version);
      $features = fixIOSFeatures($features);
      #$hardware = fixIOSHardware($hardware);

      if(strstr($ciscomodel, "OID")){ unset($ciscomodel); }
      if(!strstr($ciscomodel, " ") && strlen($ciscomodel) >= '3') {
        $hardware = $ciscomodel;
      }
      include("includes/polling/device-ios.inc.php");
      break;

    case "ProCurve":
      $sysDescr = str_replace(", ", ",", $sysDescr);
      list($hardware, $features, $version) = explode(",", $sysDescr);
      list($version) = explode("(", $version);
      if(!strstr($ciscomodel, " ")) {
        $hardware = str_replace("\"", "", $ciscomodel);
      }
      include("includes/polling/device-procurve.inc.php");
      break;
    case "Snom":
      include("includes/polling/device-snom.inc.php");
      break;
    default:
      pollDevice();
    }   
    $location = str_replace("\"","", $sysLocation); 
  
  echo("Polling temperatures\n");
  include("includes/polling/temperatures.inc.php");
  include("includes/polling/device-netstats.inc.php");
  echo("Polling interfaces\n");
  $where = "WHERE device_id = '" . $device['device_id'] . "'";
  include("includes/polling/interfaces.inc.php");

  } else {
    $status = '0';
  }

  unset( $update ) ;
  unset( $seperator) ;

  if ( $sysContact && $sysContact != $device['sysContact'] ) {
    $update .= $seperator . "`sysContact` = '$sysContact'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'Contact -> $sysContact')");
  }

  echo("$update\n");

  if ( $sysDescr && $sysDescr != $device['sysDescr'] ) {
    $update .= $seperator . "`sysDescr` = '$sysDescr'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'sysDescr -> $sysDescr')");
  }

  if ( $location && $device['location'] != $location ) {
    $update .= $seperator . "`location` = '$location'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'Location -> $location')");
  }

  if ( $version && $device['version'] != $version ) {
    $update .= $seperator . "`version` = '$version'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'OS Version -> $version')");
  }

  if ( $features && $features != $device['features'] ) {
    $update .= $seperator . "`features` = '$features'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'OS Features -> $features')");
  }

  if ( $hardware && $hardware != $device['hardware'] ) {
    $update .= $seperator . "`hardware` = '$hardware'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'Hardware -> $hardware')");
  }

  if( $device['status'] != $status ) {
    $update .= $seperator . "`status` = '$status'";
    $seperator = ", ";
    if ($status == '1') { $stat = "Up"; 
      mysql_query("INSERT INTO alerts (importance, device_id, message) VALUES ('0', '" . $device['device_id'] . "', 'Device is up\n')");
    } else { 
      $stat = "Down"; 
      mysql_query("INSERT INTO alerts (importance, device_id, message) VALUES ('9', '" . $device['device_id'] . "', 'Device is down\n')");
    }
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'Device status changed to $stat')");
  }


  if ($uptime) {


    $old_uptime = mysql_result(mysql_query("SELECT `attrib_value` FROM `devices_attribs` WHERE `device_id` = '" . $device['device_id'] . "' AND `attrib_type` = 'uptime'"), 0);

    if( $uptime < $old_uptime ) {
      if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
      mail($notify_email, "Device Rebooted: " . $device['hostname'], "Device Rebooted :" . $device['hostname'] . " at " . date('l dS F Y h:i:s A'), $config['email_headers']);
    }

    $uptimerrd = "rrd/" . $device['hostname'] . "-uptime.rrd";
    if(!is_file($uptimerrd)) {
      $woo = `rrdtool create $uptimerrd \
        DS:uptime:GAUGE:600:0:U \
        RRA:AVERAGE:0.5:1:600 \
        RRA:AVERAGE:0.5:6:700 \
        RRA:AVERAGE:0.5:24:775 \
        RRA:AVERAGE:0.5:288:797`;
    }
    rrdtool_update($uptimerrd, "N:$uptime");

    $update_uptime_attrib = mysql_query("UPDATE devices_attribs SET attrib_value = '$uptime' WHERE `device_id` = '" . $device['device_id'] . "' AND `attrib_type` = 'uptime'");
    if(mysql_affected_rows() == '0') {
      $insert_uptime_attrib = mysql_query("INSERT INTO devices_attribs (`device_id`, `attrib_type`, `attrib_value`) VALUES ('" . $device['device_id'] . "', 'uptime', '$uptime')");
    }

  }


  if ($update) {
    $update_query  = "UPDATE `devices` SET ";
    $update_query .= $update;
    $update_query .= " WHERE `device_id` = '" . $device['device_id'] . "'";
    echo("Updating " . $device['hostname'] . "\n" . $update_query . "\n");
    $update_result = mysql_query($update_query);
  } else {
    echo("No Changes to " . $device['hostname'] . "\n");
  }

  echo("\n");

}   

?>
