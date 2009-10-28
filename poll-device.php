#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");
include("includes/functions-poller.inc.php");

$poller_start = utime();

echo("Observer Poller v".$config['version']."\n\n");

$options = getopt("h:t:i:n:d::a::");

if ($options['h'] == "odd") {
  $where = "AND MOD(device_id,2) = 1";
  $doing = $options['h'];
} elseif ($options['h'] == "even") {
  $where = "AND MOD(device_id,2) = 0";
  $doing = $options['h'];
} elseif($options['h']) {
  $where = "AND `device_id` = '".$options['h']."'";
  $doing = "Host ".$options['h'];
} elseif ($options['i'] && isset($options['n'])) {
  $where = "AND MOD(device_id,".$options['i'].") = '" . $options['n'] . "'";
  $doing = "Proc ".$options['n'] ."/".$options['i'];
} elseif ($options['h'] == "all") {
  $where = " ";
  $doing = "all";
}

echo("Starting polling run:\n\n");
$i = 0;
$device_query = mysql_query("SELECT * FROM `devices` WHERE `ignore` = '0' $where  ORDER BY `device_id` ASC");
while ($device = mysql_fetch_array($device_query)) {

  echo("Polling " . $device['hostname'] . " ( device_id ".$device['device_id']." )\n\n");

  unset($update); unset($update_query); unset($seperator); unset($version); unset($uptime); unset($features); 
  unset($sysLocation); unset($hardware); unset($sysDescr); unset($sysContact); unset($sysName);

  $pingable = isPingable($device['hostname']);

  $host_rrd = $config['rrd_dir'] . "/" . $device['hostname'];

  if(!is_dir($host_rrd)) { mkdir($host_rrd); echo("Created directory : $host_rrd\n"); }

  if($pingable) { echo("Pings : yes :)\n"); } else { echo("Pings : no :(\n"); }

  $snmpable = FALSE;

  if($pingable) {
    $snmpable = isSNMPable($device['hostname'], $device['community'], $device['snmpver'], $device['port']);
    if($snmpable) { echo("SNMP : yes :)\n"); } else { echo("SNMP : no :(\n"); }
  }

  unset($snmpdata);

  if ($snmpable) { 

    $status = '1';
    if($device['os'] == "FreeBSD" || $device['os'] == "OpenBSD" || $device['os'] == "Linux" || $device['os'] == "Windows" || $device['os'] == "Voswall") { 
      $uptimeoid = ".1.3.6.1.2.1.25.1.1.0"; 
    } else { 
      $uptimeoid = "1.3.6.1.2.1.1.3.0"; 
    }
    $snmp_cmd =  $config['snmpget'] . " -m SNMPv2-MIB:HOST-RESOURCES-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .  $device['hostname'].":".$device['port'];
    $snmp_cmd .= " $uptimeoid sysLocation.0 sysContact.0 sysName.0";
    #$snmp_cmd .= " | grep -v 'Cisco Internetwork Operating System Software'";
    if($device['os'] == "IOS" || $device['os'] == "IOS XE") {       
      $snmp_cmdb =  $config['snmpget'] . " -m ENTITY-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .  $device['hostname'].":".$device['port'];
      $snmp_cmdb .= " .1.3.6.1.2.1.47.1.1.1.1.13.1 .1.3.6.1.2.1.47.1.1.1.1.4.1 .1.3.6.1.2.1.47.1.1.1.1.13.1001 .1.3.6.1.2.1.47.1.1.1.1.4.1001";
      list($a,$b,$c,$d) = explode("\n", shell_exec($snmp_cmdb));
      if($b == "0") { $ciscomodel = $a; }
      if($d == "0") { $ciscomodel = $c; }
      $ciscomodel = str_replace("\"","",$ciscomodel);
    } else { unset($ciscomodel); }

    $snmpdata = shell_exec($snmp_cmd);
    #$snmpdata = preg_replace("/^.*IOS/","", $snmpdata);
    $snmpdata = trim($snmpdata);
    $snmpdata = str_replace("\"", "", $snmpdata);
    list($sysUptime, $sysLocation, $sysContact, $sysName) = explode("\n", $snmpdata);
    $sysDescr = trim(shell_exec($config['snmpget'] . " -m SNMPv2-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .  $device['hostname'].":".$device['port'] . " sysDescr.0"));
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
      include("includes/polling/device-unix.inc.php");
      break;

    case "Windows":
      include("includes/polling/device-windows.inc.php");
      break;

    case "ScreenOS":
      include("includes/polling/device-screenos.inc.php");
      break;

    case "Fortigate":
      include("includes/polling/device-fortigate.inc.php");
      break;

    case "JunOS":
      include("includes/polling/device-junos.inc.php");
      break;

    case "IOS":
    case "IOS XE":
      include("includes/polling/device-ios.inc.php");
      break;

    case "CatOS":
      include("includes/polling/device-catos.inc.php");
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

    case "BCM96348":
      include("includes/polling/adslline.inc.php");
    break;

    case "Snom":
      include("includes/polling/device-snom.inc.php");
      break;

    default:
      pollDevice();
    }   
    $sysLocation = str_replace("\"","", $sysLocation); 
  
  echo("Polling temperatures\n");
  include("includes/polling/temperatures.inc.php");
  include("includes/polling/device-netstats.inc.php");

#  echo("Polling interfaces\n");
#  $where = "WHERE device_id = '" . $device['device_id'] . "' AND deleted = '0'";
#  include("includes/polling/interfaces.inc.php");

   include("includes/polling/ports.inc.php");
   include("includes/polling/ports-etherlike.inc.php");
   include("includes/polling/cisco-mac-accounting.inc.php");



    $update_uptime_attrib = mysql_query("UPDATE devices_attribs SET attrib_value = NOW() WHERE `device_id` = '" . $device['device_id'] . "' AND `attrib_type` = 'polled'");
    if(mysql_affected_rows() == '0') {
      $insert_uptime_attrib = mysql_query("INSERT INTO devices_attribs (`device_id`, `attrib_type`, `attrib_value`) VALUES ('" . $device['device_id'] . "', 'polled', NOW())");
    }




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

  $sysName = strtolower($sysName);

  if ( $sysName && $sysName != $device['sysName'] ) {
    $update .= $seperator . "`sysName` = '$sysName'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'sysName -> $sysName')");
  }

  if ( $sysDescr && $sysDescr != $device['sysDescr'] ) {
    $update .= $seperator . "`sysDescr` = '$sysDescr'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'sysDescr -> $sysDescr')");
  }

  if ( $sysLocation && $device['location'] != $sysLocation ) {
    $update .= $seperator . "`location` = '$sysLocation'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'Location -> $sysLocation')");
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

    $old_uptime = @mysql_result(mysql_query("SELECT `attrib_value` FROM `devices_attribs` WHERE `device_id` = '" . $device['device_id'] . "' AND `attrib_type` = 'uptime'"), 0);

    if( $uptime < $old_uptime ) {
      if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
      mail($notify_email, "Device Rebooted: " . $device['hostname'], "Device Rebooted :" . $device['hostname'] . " at " . date('l dS F Y h:i:s A'), $config['email_headers']);
    }

    $uptimerrd    = $config['rrd_dir'] . "/" . $device['hostname'] . "/uptime.rrd";

    $old_uptimerrd = "rrd/" . $device['hostname'] . "-uptime.rrd";
    if(is_file($old_uptimerrd) && !is_file($uptimerrd)) { rename($old_uptimerrd, $uptimerrd); echo("Moving $old_uptimerrd to $uptimerrd");  }

    if(!is_file($uptimerrd)) {
      $woo = shell_exec($config['rrdtool'] . " create $uptimerrd \
        DS:uptime:GAUGE:600:0:U \
        RRA:AVERAGE:0.5:1:600 \
        RRA:AVERAGE:0.5:6:700 \
        RRA:AVERAGE:0.5:24:775 \
        RRA:AVERAGE:0.5:288:797");
    }
    rrdtool_update($uptimerrd, "N:$uptime");

    $update_uptime_attrib = mysql_query("UPDATE devices_attribs SET attrib_value = '$uptime' WHERE `device_id` = '" . $device['device_id'] . "' AND `attrib_type` = 'uptime'");
    if(mysql_affected_rows() == '0') {
      $insert_uptime_attrib = mysql_query("INSERT INTO devices_attribs (`device_id`, `attrib_type`, `attrib_value`) VALUES ('" . $device['device_id'] . "', 'uptime', '$uptime')");
    }

    mysql_query("UPDATE `devices` SET `last_polled` = NOW() WHERE `device_id` = '". $device['device_id'] ."'");

  } ## End if snmpable


  if ($update) {
    $update_query  = "UPDATE `devices` SET ";
    $update_query .= $update;
    $update_query .= " WHERE `device_id` = '" . $device['device_id'] . "'";
    echo("Updating " . $device['hostname'] . "\n");
    $update_result = mysql_query($update_query);
  } else {
    echo("No Changes to " . $device['hostname'] . "\n");
  }
  $i++;
  echo("\n");

}   

$poller_end = utime(); $poller_run = $poller_end - $poller_start; $poller_time = substr($poller_run, 0, 5);

$string = $argv[0] . " $doing " .  date("F j, Y, G:i") . " - $i devices polled in $poller_time secs";
echo("$string\n");
shell_exec("echo '".$string."' >> /opt/observer/observer.log");


?>
