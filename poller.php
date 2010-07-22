#!/usr/bin/php
<?php

### Observium Device Poller

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

$poller_start = utime();
echo("Observium Poller v".$config['version']."\n\n");

$options = getopt("h:t:i:n:d::a::");

if ($options['h'] == "odd")      { $options['n'] = "1"; $options['i'] = "2"; }
elseif ($options['h'] == "even") { $options['n'] = "0"; $options['i'] = "2"; }
elseif ($options['h'] == "all")  { $where = " "; $doing = "all"; }
elseif ($options['h']) {
  if (is_numeric($options['h']))
  {
    $where = "AND `device_id` = '".$options['h']."'";  
    $doing = $options['h'];
  }
  else
  {
    $where = "AND `hostname` LIKE '".str_replace('*','%',mres($options['h']))."'";  
    $doing = $options['h'];
  }
}

if ($options['i'] && isset($options['n'])) {
  $where = "AND MOD(device_id,".$options['i'].") = '" . $options['n'] . "'";  
  $doing = $options['n'] ."/".$options['i'];
}

if (!$where) {
  echo("-h <device id> | <device hostname>  Poll single device\n");
  echo("-h odd                              Poll odd numbered devices  (same as -i 2 -n 0)\n");
  echo("-h even                             Poll even numbered devices (same as -i 2 -n 1)\n");
  echo("-h all                              Poll all devices\n\n");
  echo("-i <instances> -n <number>          Poll as instance <number> of <instances>\n");
  echo("                                    Instances start at 0. 0-3 for -n 4\n\n");
  echo("-d                                  Enable some debugging output\n");
  echo("\n");
  echo("No polling type specified!\n");
  exit;
 }

if (isset($options['d'])) { echo("DEBUG!\n"); $debug = 1; }


echo("Starting polling run:\n\n");
$polled_devices = 0;
$device_query = mysql_query("SELECT * FROM `devices` WHERE `ignore` = 0 AND `disabled` = 0 $where  ORDER BY `device_id` ASC");
while ($device = mysql_fetch_array($device_query)) 
{
  $status = 0; unset($array);
  $device_start = utime();  // Start counting device poll time

  echo($device['hostname'] . " ".$device['device_id']." ".$device['os']." ");
  if($config['os'][$device['os']]['group']) {$device['os_group'] = $config['os'][$device['os']]['group']; echo "(".$device['os_group'].")";}
  echo("\n");

  unset($poll_update); unset($poll_update_query); unset($poll_separator); unset($version); unset($uptime); unset($features); 
  unset($sysLocation); unset($hardware); unset($sysDescr); unset($sysContact); unset($sysName); unset($serial);

  $host_rrd = $config['rrd_dir'] . "/" . $device['hostname'];
  if (!is_dir($host_rrd)) { mkdir($host_rrd); echo("Created directory : $host_rrd\n"); }


  $device['pingable'] = isPingable($device['hostname']);
  if($device['pingable']) {
    $device['snmpable'] = isSNMPable($device['hostname'], $device['community'], $device['snmpver'], $device['port']);
    if($device['snmpable']) {
      $status = "1";
    } else {
      echo("SNMP Unreachable");
      $status = "0";
    }
  } else {
    echo("Unpingable");
    $status = "0";
  }

  if ( $device['status'] != $status )
  {
    $poll_update .= $poll_separator . "`status` = '$status'";
    $poll_separator = ", ";
    mysql_query("UPDATE `devices` SET `status` = '".$status."' WHERE `device_id` = '".$device['device_id']."'");
    mysql_query("INSERT INTO alerts (importance, device_id, message) VALUES ('0', '" . $device['device_id'] . "', 'Device is " .($status == '1' ? 'up' : 'down') . "')");
    log_event('Device status changed to ' . ($status == '1' ? 'Up' : 'Down'), $device['device_id'], ($status == '1' ? 'up' : 'down'));
    notify($device, "Device ".($status == '1' ? 'Up' : 'Down').": " . $device['hostname'], "Device ".($status == '1' ? 'up' : 'down').": " . $device['hostname'] . " at " . date($config['timestamp_format']));
  }

  if ($status == "1") 
  { 
    $snmp_cmd =  $config['snmpget'] . " -m SNMPv2-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .  $device['hostname'].":".$device['port'];
    $snmp_cmd .= " sysUpTime.0 sysLocation.0 sysContact.0 sysName.0";
    $snmpdata = str_replace('"','',trim(shell_exec($snmp_cmd)));
    list($sysUptime, $sysLocation, $sysContact, $sysName) = explode("\n", $snmpdata);
    $snmp_cmd =  $config['snmpget'] . " -m SNMPv2-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .  $device['hostname'].":".$device['port'];
    $snmp_cmd .= " HOST-RESOURCES-MIB::hrSystemUptime.0";
    $snmpdata = str_replace('"','',trim(shell_exec($snmp_cmd)));
    list($hrSystemUptime) = explode("\n", $snmpdata);
    $sysDescr = trim(shell_exec($config['snmpget'] . " -m SNMPv2-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .  $device['hostname'].":".$device['port'] . " sysDescr.0"));
    $sysName = strtolower($sysName);

    if ($hrSystemUptime != "No Such Object available on this agent at this OID" && $hrSystemUptime != "")
    {
      #HOST-RESOURCES-MIB::hrSystemUptime.0 = Timeticks: (63050465) 7 days, 7:08:24.65
      $hrSystemUptime = str_replace("(", "", $hrSystemUptime);
      $hrSystemUptime = str_replace(")", "", $hrSystemUptime); 
      list($days,$hours, $mins, $secs) = explode(":", $hrSystemUptime);
      list($secs, $microsecs) = explode(".", $secs);
      $hours = $hours + ($days * 24);
      $mins = $mins + ($hours * 60);
      $secs = $secs + ($mins * 60);
      $uptime = $secs;
      if ($device['os'] == "windows") { $uptime /= 10; }
    }
    else
    {
      #SNMPv2-MIB::sysUpTime.0 = Timeticks: (2542831) 7:03:48.31
      $sysUptime = str_replace("(", "", $sysUptime);
      $sysUptime = str_replace(")", "", $sysUptime); 
      list($days, $hours, $mins, $secs) = explode(":", $sysUptime);
      list($secs, $microsecs) = explode(".", $secs);
      $hours = $hours + ($days * 24);
      $mins = $mins + ($hours * 60);
      $secs = $secs + ($mins * 60);
      $uptime = $secs;
    }

    if ($uptime) 
    {
      if ( $uptime < $device['uptime'] ) {
        notify($device,"Device rebooted: " . $device['hostname'],  "Device Rebooted : " . $device['hostname'] . " " . formatUptime($uptime) . " ago.");
        log_event('Device rebooted after '.formatUptime($device['uptime']), $device['device_id'], 'reboot', $device['uptime']);
      }
  
      $uptimerrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/uptime.rrd";
  
      if (!is_file($uptimerrd)) 
      {
        $woo = shell_exec($config['rrdtool'] . " create $uptimerrd \
        DS:uptime:GAUGE:600:0:U \
        RRA:AVERAGE:0.5:1:600 \
        RRA:AVERAGE:0.5:6:700 \
        RRA:AVERAGE:0.5:24:775 \
        RRA:AVERAGE:0.5:288:797");
      }
      rrdtool_update($uptimerrd, "N:$uptime");

      $poll_update .= $poll_separator . "`uptime` = '$uptime'";
      $poll_separator = ", ";
    } 

    if (is_file($config['install_dir'] . "/includes/polling/os/".$device['os'].".inc.php")) 
    {
      /// OS Specific
      include($config['install_dir'] . "/includes/polling/os/".$device['os'].".inc.php");
    }
    elseif ($device['os_group'] && is_file($config['install_dir'] . "/includes/polling/os/".$device['os_group'].".inc.php")) 
    {
      /// OS Group Specific
      include($config['install_dir'] . "/includes/polling/os/".$device['os_group'].".inc.php");
    }
    else
    {
      echo("Generic :(\n");
    }

    echo("Hardware: ".$hardware." Version: ".$version." Features: ".$features."\n");

    $sysLocation = str_replace("\"","", $sysLocation); 
  
    include("includes/polling/temperatures.inc.php");
    include("includes/polling/humidity.inc.php");
    include("includes/polling/fanspeeds.inc.php");
    include("includes/polling/voltages.inc.php");
    include("includes/polling/frequencies.inc.php");
    include("includes/polling/current.inc.php");
    include("includes/polling/processors.inc.php");
    include("includes/polling/mempools.inc.php");
    include("includes/polling/storage.inc.php");
    include("includes/polling/netstats.inc.php");
    include("includes/polling/ipSystemStats.inc.php");
    include("includes/polling/ports.inc.php");
    include("includes/polling/altiga-ssl.inc.php");
    include("includes/polling/cisco-mac-accounting.inc.php");
    include("includes/polling/bgpPeer.inc.php");
    include("includes/polling/toner.inc.php");
    include("includes/polling/ucd-diskio.inc.php");
    include("includes/polling/applications.inc.php");
    include("includes/polling/wireless.inc.php");

    unset( $update ) ;
    unset( $seperator) ;
  
    if ( $serial && $serial != $device['serial'] ) {
      $poll_update .= $poll_separator . "`serial` = '".mres($serial)."'";
      $poll_separator = ", ";
      log_event("Serial -> $serial", $device['device_id'], 'system');
    }
  
    if ( $sysContact && $sysContact != $device['sysContact'] ) {
      $poll_update .= $poll_separator . "`sysContact` = '".mres($sysContact)."'";
      $poll_separator = ", ";
      log_event("Contact -> $sysContact", $device['device_id'], 'system');
    }
  
    if ( $sysName && $sysName != $device['sysName'] ) {
      $poll_update .= $poll_separator . "`sysName` = '$sysName'";
      $poll_separator = ", ";
      log_event("sysName -> $sysName", $device['device_id'], 'system');
    }
  
    if ( $sysDescr && $sysDescr != $device['sysDescr'] ) {
      $poll_update .= $poll_separator . "`sysDescr` = '$sysDescr'";
      $poll_separator = ", ";
      log_event("sysDescr -> $sysDescr", $device['device_id'], 'system');
    }
  
    if ( $sysLocation && $device['location'] != $sysLocation ) {
      $poll_update .= $poll_separator . "`location` = '$sysLocation'";
      $poll_separator = ", ";
      log_event("Location -> $sysLocation", $device['device_id'], 'system');
    }
  
    if ( $version && $device['version'] != $version ) {
      $poll_update .= $poll_separator . "`version` = '$version'";
      $poll_separator = ", ";
      log_event("OS Version -> $version", $device['device_id'], 'system');
    }
  
    if ( $features != $device['features'] ) {
      $poll_update .= $poll_separator . "`features` = '$features'";
      $poll_separator = ", ";
      log_event("OS Features -> $features", $device['device_id'], 'system');
    }
  
    if ( $hardware && $hardware != $device['hardware'] ) {
      $poll_update .= $poll_separator . "`hardware` = '$hardware'";
      $poll_separator = ", ";
      log_event("Hardware -> $hardware", $device['device_id'], 'system');
    }

    $poll_update .= $poll_separator . "`last_polled` = NOW()";
    $poll_separator = ", ";
    $polled_devices++;
    echo("\n");
  } 

  $device_end = utime(); $device_run = $device_end - $device_start; $device_time = substr($device_run, 0, 5);
  $poll_update .= $poll_separator . "`last_polled_timetaken` = '$device_time'";
  echo("$device_end - $device_start; $device_time $device_run");
  echo("Polled in $device_time seconds\n");


  $poll_update_query  = "UPDATE `devices` SET ";
  $poll_update_query .= $poll_update;
  $poll_update_query .= " WHERE `device_id` = '" . $device['device_id'] . "'";
  if($debug) {echo("Updating " . $device['hostname'] . " - $poll_update_query \n");}
  $poll_update_result = mysql_query($poll_update_query);
  if(mysql_affected_rows() == "1") { echo("UPDATED!\n"); } else { echo("NOT UPDATED!\n"); }
  
  unset($storage_cache); // Clear cache of hrStorage ** MAYBE FIXME? **
  unset($cache); // Clear cache (unify all things here?)

}   

$poller_end = utime(); $poller_run = $poller_end - $poller_start; $poller_time = substr($poller_run, 0, 5);

if($polled_devices) {
  mysql_query("INSERT INTO `perf_times` (`type`, `doing`, `start`, `duration`, `devices`) 
                               VALUES ('poll', '$doing', '$poller_start', '$poller_time', '$polled_devices')");
}

$string = $argv[0] . " $doing " .  date("F j, Y, G:i") . " - $polled_devices devices polled in $poller_time secs";
if ($debug) echo("$string\n");
shell_exec("echo '".$string."' >> ".$config['log_file']); # FIXME EWW

?>
