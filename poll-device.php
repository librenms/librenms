#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");
include("includes/functions-poller.inc.php");

$poller_start = utime();
echo("Observer Poller v".$config['version']."\n\n");

$options = getopt("h:t:i:n:d::a::");

if ($options['h'] == "odd") {
  $where = "AND MOD(device_id,2) = 1";  $doing = $options['h'];
} elseif ($options['h'] == "even") {
  $where = "AND MOD(device_id,2) = 0";  $doing = $options['h'];
} elseif ($options['h'] == "all") {
  $where = " ";  $doing = "all";
} elseif($options['h']) {
  $where = "AND `device_id` = '".$options['h']."'";  $doing = "Host ".$options['h'];
} elseif ($options['i'] && isset($options['n'])) {
  $where = "AND MOD(device_id,".$options['i'].") = '" . $options['n'] . "'";  $doing = "Proc ".$options['n'] ."/".$options['i'];
}

if(!$where) {
  echo("-h <device id>                Poll single device\n");
  echo("-h odd                        Poll odd numbered devices  (same as -i 2 -n 0)\n");
  echo("-h even                       Poll even numbered devices (same as -i 2 -n 1)\n");
  echo("-h all                        Poll all devices\n\n");
  echo("-i <instances> -n <number>    Poll as instance <number> of <instances>\n");
  echo("                              Instances start at 0. 0-3 for -n 4\n\n");
  echo("-d                            Enable some debugging output\n");
  echo("\n");
  echo("No polling type specified!\n");
  exit;
 }

if(isset($options['d'])) { echo("DEBUG!\n"); $debug = 1; }


echo("Starting polling run:\n\n");
$i = 0;
$device_query = mysql_query("SELECT * FROM `devices` WHERE `ignore` = '0' $where  ORDER BY `device_id` ASC");
while ($device = mysql_fetch_array($device_query)) {

  echo($device['hostname'] . " ".$device['device_id']." ".$device['os']." ");
  if($os_groups[$device[os]]) {$device['os_group'] = $os_groups[$device[os]]; echo "(".$device['os_group'].")";}
  echo("\n");

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
    $snmp_cmd =  $config['snmpget'] . " -m SNMPv2-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .  $device['hostname'].":".$device['port'];
    $snmp_cmd .= " sysUpTime.0 sysLocation.0 sysContact.0 sysName.0";
    $snmpdata = shell_exec($snmp_cmd);
    #$snmpdata = preg_replace("/^.*IOS/","", $snmpdata);
    $snmpdata = trim($snmpdata);
    $snmpdata = str_replace("\"", "", $snmpdata);
    list($sysUptime, $sysLocation, $sysContact, $sysName) = explode("\n", $snmpdata);
    $sysDescr = trim(shell_exec($config['snmpget'] . " -m SNMPv2-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .  $device['hostname'].":".$device['port'] . " sysDescr.0"));
    $sysUptime = str_replace("(", "", $sysUptime);
    $sysUptime = str_replace(")", "", $sysUptime); 
    $sysName = strtolower($sysName);
    list($days, $hours, $mins, $secs) = explode(":", $sysUptime);
    list($secs, $microsecs) = explode(".", $secs);
    $hours = $hours + ($days * 24);
    $mins = $mins + ($hours * 60);
    $secs = $secs + ($mins * 60);
    $uptime = $secs;

    if(is_file($config['install_dir'] . "/includes/polling/device-".$device['os'].".inc.php")) {
      /// OS Specific
      include($config['install_dir'] . "/includes/polling/device-".$device['os'].".inc.php");
    }elseif($device['os_group'] && is_file($config['install_dir'] . "/includes/polling/device-".$device['os_group'].".inc.php")) {
      /// OS Group Specific
      include($config['install_dir'] . "/includes/polling/device-".$device['os_group'].".inc.php");
    }else{
      echo("Generic :(");
    }


    $sysLocation = str_replace("\"","", $sysLocation); 
  
    include("includes/polling/temperatures.inc.php");
    include("includes/polling/device-netstats.inc.php");
    include("includes/polling/ipSystemStats.inc.php");
    include("includes/polling/ports.inc.php");
    include("includes/polling/cisco-mac-accounting.inc.php");
    include("includes/polling/bgpPeer.inc.php");

  unset( $update ) ;
  unset( $seperator) ;

  if ( $sysContact && $sysContact != $device['sysContact'] ) {
    $update .= $seperator . "`sysContact` = '".mres($sysContact)."'";
    $seperator = ", ";
    mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'Contact -> $sysContact')");
  }

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

    if( $uptime < $device['uptime'] ) {
      if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
      mail($email, "Device Rebooted: " . $device['hostname'], "Device Rebooted : " . $device['hostname'] . " " . duration($uptime) . " ago.", $config['email_headers']);
      mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $device['device_id'] . "', '', NOW(), 'Device rebooted')");
    }

    $uptimerrd    = $config['rrd_dir'] . "/" . $device['hostname'] . "/uptime.rrd";

    if(!is_file($uptimerrd)) {
      $woo = shell_exec($config['rrdtool'] . " create $uptimerrd \
        DS:uptime:GAUGE:600:0:U \
        RRA:AVERAGE:0.5:1:600 \
        RRA:AVERAGE:0.5:6:700 \
        RRA:AVERAGE:0.5:24:775 \
        RRA:AVERAGE:0.5:288:797");
    }
    rrdtool_update($uptimerrd, "N:$uptime");

    $update .= $seperator . "`uptime` = '$uptime'";
    $seperator = ", ";
  } 
  $update .= $seperator . "`last_polled` = NOW()";
  if ($update) {
    $update_query  = "UPDATE `devices` SET ";
    $update_query .= $update;
    $update_query .= " WHERE `device_id` = '" . $device['device_id'] . "'";
    echo("Updating " . $device['hostname'] . " - $update_query \n");
    $update_result = mysql_query($update_query);
  } else {
    echo("No Changes to " . $device['hostname'] . "\n");
  }
  $i++;
  echo("\n");
  } else {
    $update_query  = "UPDATE `devices` SET ";
    $update_query .= " `status` = '0'";
    $update_query .= " WHERE `device_id` = '" . $device['device_id'] . "'";
    echo("Updating " . $device['hostname'] . "\n");
    $update_result = mysql_query($update_query);
  }
}   

$poller_end = utime(); $poller_run = $poller_end - $poller_start; $poller_time = substr($poller_run, 0, 5);

$string = $argv[0] . " $doing " .  date("F j, Y, G:i") . " - $i devices polled in $poller_time secs";
if ($debug) echo("$string\n");
shell_exec("echo '".$string."' >> ".$config['install_dir']."/observer.log");


?>
