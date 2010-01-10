<?php

## Include from PEAR

include_once("Net/IPv4.php");
include_once("Net/IPv6.php");

## Observer Includes

include_once($config['install_dir'] . "/includes/common.php");

include_once($config['install_dir'] . "/includes/generic.php");
include_once($config['install_dir'] . "/includes/procurve.php");
include_once($config['install_dir'] . "/includes/graphing.php");
include_once($config['install_dir'] . "/includes/print-functions.php");
include_once($config['install_dir'] . "/includes/billing.php");
include_once($config['install_dir'] . "/includes/cisco-entities.php");
include_once($config['install_dir'] . "/includes/syslog.php");
include_once($config['install_dir'] . "/includes/rewrites.php");

## CollectD

require('collectd/config.php');
require('collectd/functions.php');
require('collectd/definitions.php');

function mac_clean_to_readable($mac){

   $r = substr($mac, 0, 2);
   $r .= ":".substr($mac, 2, 2);
   $r .= ":".substr($mac, 4, 2);
   $r .= ":".substr($mac, 6, 2);
   $r .= ":".substr($mac, 8, 2);
   $r .= ":".substr($mac, 10, 2);

   return($r);
}

function zeropad($num)
{
    return (strlen($num) == 1) ? '0'.$num : $num;
}

function zeropad_lineno($num, $length)
{
    while (strlen($num) < $length)
        $num = '0'.$num;
   
    return $num;
}

function only_alphanumeric( $string )
{
        return preg_replace('/[^a-zA-Z0-9]/', '', $string);
}


function validate_hostip($host) {

}

function write_dev_attrib($device_id, $attrib_type, $attrib_value) {
  $count_sql = "SELECT COUNT(*) FROM devices_attribs WHERE `device_id` = '" . $device_id . "' AND `attrib_type` = '$attrib_type'";
  if(mysql_result(mysql_query($count_sql),0)) {
    $update_sql = "UPDATE devices_attribs SET attrib_value = '$attrib_value' WHERE `device_id` = '$device_id' AND `attrib_type` = '$attrib_type'";
    mysql_query($update_sql);
  } else {
    $insert_sql = "INSERT INTO devices_attribs (`device_id`, `attrib_type`, `attrib_value`) VALUES ('$device_id', '$attrib_type', '$attrib_value')";
    mysql_query($insert_sql);
  }
  return mysql_affected_rows();
}

function shorthost($hostname, $len=16) {
  list ($first, $second, $third, $fourth, $fifth) = explode(".", $hostname);
  $shorthost = $first;
  if(strlen($first.".".$second) < $len && $second) { 
    $shorthost = $first.".".$second; 
    if(strlen($shorthost.".".$third) < $len && $third) {
      $shorthost = $shorthost.".".$third;
      if(strlen($shorthost.".".$fourth) < $len && $fourth) {
        $shorthost = $shorthost.".".$fourth;
        if(strlen($shorthost.".".$fifth) < $len && $fifth) {
          $shorthost = $shorthost.".".$fifth;
        }
      }
    }
  }
  return ($shorthost);
}

function rrdtool_update($rrdfile, $rrdupdate) {
  global $config;
  global $debug;
  if($debug) { echo($config['rrdtool'] . " update $rrdfile $rrdupdate \n"); }
  return shell_exec($config['rrdtool'] . " update $rrdfile $rrdupdate");
}

function rrdtool($command, $file, $options) {
  global $config;
  if($config['debug']) { echo($config['rrdtool'] . " $command $file $options \n"); }
  return shell_exec($config['rrdtool'] . " $command $file $options");
}

function device_array($device_id) {
  $sql = "SELECT * FROM `devices` WHERE `device_id` = '".$device_id."'";
  $query = mysql_query($sql);
  $device = mysql_fetch_array($query);
  return $device;
}

function getHostOS($device) {
    global $config;
    $sysDescr_cmd = $config['snmpget']." -m SNMPv2-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " sysDescr.0";
    $sysDescr = str_replace("\"", "", trim(shell_exec($sysDescr_cmd)));
    $dir_handle = @opendir($config['install_dir'] . "/includes/osdiscovery") or die("Unable to open $path");
    while ($file = readdir($dir_handle)) {
      if( preg_match("/^discover-([a-z0-9]*).php/", $file) ) {
        include($config['install_dir'] . "/includes/osdiscovery/" . $file);
      }
    }
    closedir($dir_handle);
    if($os) { return $os; } else { return FALSE; }
}

function billpermitted($bill_id) 
{
  global $_SESSION;
  if($_SESSION['userlevel'] >= "5") {
    $allowed = TRUE;
  } elseif (@mysql_result(mysql_query("SELECT count(*) FROM bill_perms WHERE `user_id` = '" . $_SESSION['user_id'] . "' AND `bill_id` = $bill_id"), 0) > '0') {
    $allowed = TRUE;
  } else {
    $allowed = FALSE;
  }
  return $allowed;

}


function interfacepermitted($interface_id) 
{
  global $_SESSION;
  if($_SESSION['userlevel'] >= "5") { 
    $allowed = TRUE; 
  } elseif ( devicepermitted(mysql_result(mysql_query("SELECT `device_id` FROM `interfaces` WHERE `interface_id` = '$interface_id'"),0))) {
    $allowed = TRUE;
  } elseif ( @mysql_result(mysql_query("SELECT `interface_id` FROM `interfaces_perms` WHERE `user_id` = '" . $_SESSION['user_id'] . "' AND `interface_id` = $interface_id"), 0)) {
    $allowed = TRUE;
  } else { 
    $allowed = FALSE; 
  }
  return $allowed;
}

function devicepermitted($device_id) 
{
  global $_SESSION;
  if($_SESSION['userlevel'] >= "5") { 
    $allowed = true; 
  } elseif ( @mysql_result(mysql_query("SELECT * FROM devices_perms WHERE `user_id` = '" . $_SESSION['user_id'] . "' AND `device_id` = $device_id"), 0) > '0' ) {
    $allowed = true;
  } else { 
    $allowed = false; 
  }
  return $allowed;

}

function formatRates($rate) {
   $rate = format_si($rate) . "bps";
   return $rate;
}

function formatstorage($rate, $round = '2') 
{
   $rate = format_bi($rate, $round) . "B";
   return $rate;
}

function format_si($rate) 
{
  $sizes = Array('', 'K', 'M', 'G', 'T', 'P', 'E');
  $round = Array('0','0','0','2','2','2','2','2','2');
  $ext = $sizes[0];
  for ($i=1; (($i < count($sizes)) && ($rate >= 1000)); $i++) { $rate = $rate / 1000; $ext  = $sizes[$i]; }
  return round($rate, $round[$i]).$ext;
}

function format_bi($size, $round = '2') 
{
  $sizes = Array('', 'K', 'M', 'G', 'T', 'P', 'E');
  $ext = $sizes[0];
  for ($i=1; (($i < count($sizes)) && ($size >= 1024)); $i++) { $size = $size / 1024; $ext  = $sizes[$i];  }
  return round($size, $round).$ext;
}

function arguments($argv) 
{
    $_ARG = array();
    foreach ($argv as $arg) {
      if (ereg('--([^=]+)=(.*)',$arg,$reg)) {
        $_ARG[$reg[1]] = $reg[2];
      } elseif(ereg('-([a-zA-Z0-9])',$arg,$reg)) {
            $_ARG[$reg[1]] = 'true';
        }
   
    }
  return $_ARG;
}

function percent_colour($perc)
{
 $r = min(255, 5 * ($perc - 25));
 $b = max(0, 255 - (5 * ($perc + 25)));
 return sprintf('#%02x%02x%02x', $r, $b, $b);
} 

function print_error($text)
{
  echo("<table class=errorbox cellpadding=3><tr><td><img src='/images/15/exclamation.png' align=absmiddle> $text</td></tr></table>");
}

function print_message($text)
{
  echo("<table class=messagebox cellpadding=3><tr><td><img src='/images/16/tick.png' align=absmiddle> $text</td></tr></table>");
}

function interface_rates ($rrd_file)  // Returns the last in/out value in RRD
{
  global $config;
  #$rrdfile = $config['rrd_dir'] . "/" . $interface['hostname'] . "/" . $interface['ifIndex'] . ".rrd";
  $cmd  = $config['rrdtool']." fetch -s -600s -e now $rrd_file AVERAGE | grep : | cut -d\" \" -f 2,3 | grep e";
  $data = trim(`$cmd`);
  foreach( explode("\n", $data) as $entry) {
    list($in, $out) = split(" ", $entry);
    $rate['in'] = $in * 8;
    $rate['out'] = $out * 8;
  }
  return $rate;
}

function interface_errors ($rrd_file, $period = '-1d') // Returns the last in/out errors value in RRD
{
  global $config;
  #$rrdfile = $config['rrd_dir'] . "/" . $interface['hostname'] . "/" . $interface['ifIndex'] . ".rrd";
  $cmd = $config['rrdtool']." fetch -s $period -e -300s $rrd_file AVERAGE | grep : | cut -d\" \" -f 4,5";
  $data = trim(shell_exec($cmd));
  foreach( explode("\n", $data) as $entry) {
        list($in, $out) = explode(" ", $entry);
        $in_errors += ($in * 300);
        $out_errors += ($out * 300);
  }
  $errors['in'] = round($in_errors);
  $errors['out'] = round($out_errors);
  return $errors;
}

function interface_packets ($rrd_file) // Returns the last in/out pps value in RRD
{
  global $config;
  #$rrdfile = $config['rrd_dir'] . "/" . $interface['hostname'] . "/" . $interface['ifIndex'] . ".rrd";
  $cmd = $config['rrdtool']." fetch -s -1d -e -300s $rrd_file AVERAGE | grep : | cut -d\" \" -f 6,7";
  $data = trim(shell_exec($cmd));
  foreach( explode("\n", $data) as $entry) {
        list($in, $out) = explode(" ", $entry);
  }
  $packets['in'] = round($in);
  $packets['out'] = round($out);
  return $packets;
}

function geteventicon ($message) 
{
  if($message == "Device status changed to Down") { $icon = "server_connect.png"; }
  if($message == "Device status changed to Up") { $icon = "server_go.png"; }
  if($message == "Interface went down" || $message == "Interface changed state to Down" ) { $icon = "if-disconnect.png"; }
  if($message == "Interface went up" || $message == "Interface changed state to Up" ) { $icon = "if-connect.png"; }
  if($message == "Interface disabled") { $icon = "if-disable.png"; }
  if($message == "Interface enabled") { $icon = "if-enable.png"; }
  if($icon) { return $icon; } else { return false; }
}

function generateiflink($interface, $text=0,$type) 
{
  global $twoday; global $now; global $config; global $day; global $month;
  $interface = ifNameDescr($interface);
  if(!$text) { $text = fixIfName($interface['label']); }
  if($type) { $interface['graph_type'] = $type; }
  if(!$interface['graph_type']) { $interface['graph_type'] = 'port_bits'; }
  $class = ifclass($interface['ifOperStatus'], $interface['ifAdminStatus']);
  $graph_url = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&from=$day&to=$now&width=400&height=100&type=" . $interface['graph_type'];
  $graph_url_month = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&from=$month&to=$now&width=400&height=100&type=" . $interface['graph_type'];
  $device_id = getifhost($interface['interface_id']);
  $link = "<a class=$class href='".$config['base_url']."/device/$device_id/interface/" . $interface['interface_id'] . "/' ";
  $link .= "onmouseover=\" return overlib('";
  $link .= "<img src=\'$graph_url\'><br /><img src=\'$graph_url_month\'>', CAPTION, '<span class=list-large>" . $interface['hostname'] . " - " . fixifName($interface['label']) . "</span>";
  if($interface['ifAlias']) { $link .= "<br />" . $interface['ifAlias']; }
  $link .= "' ";
  $link .= $config['overlib_defaults'].");\" onmouseout=\"return nd();\" >$text</a>";

  return $link;
}

function generatedevicelink($device, $text=0, $start=0, $end=0) 
{
  global $twoday; global $day; global $now; global $config;
  if(!$start) { $start = $day; }
  if(!$end) { $end = $now; }
  $class = devclass($device);
  if(!$text) { $text = $device['hostname']; }
  $graph_url = $config['base_url'] . "/graph.php?device=" . $device['device_id'] . "&from=$start&to=$end&width=400&height=120&type=device_cpu";
  $graph_url_b = $config['base_url'] . "/graph.php?device=" . $device['device_id'] . "&from=$start&to=$end&width=400&height=120&type=device_memory";
  $link  = "<a class=$class href='".$config['base_url']."/device/" . $device['device_id'] . "/' ";
  $link .= "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - CPU & Memory Usage</div>";
  $link .= "<img src=\'$graph_url\'><br /><img src=\'$graph_url_b\'>'".$config['overlib_defaults'].", LEFT);\" onmouseout=\"return nd();\">$text</a>";
  return $link;
}


function device_traffic_image($device, $width, $height, $from, $to) 
{
  return "<img src='graph.php?device=" . $device . "&type=device_bits&from=" . $from . "&to=" . $to . "&width=" . $width . "&height=" . $height . "&legend=no' />";
}

function devclass($device) 
{
   if ($device['status'] == '0') { $class = "list-device-down"; } else { $class = "list-device"; }
   if ($device['ignore'] == '1') {
     $class = "list-device-ignored";
     if ($device['status'] == '1') { $class = "list-device-ignored-up"; }
   }
  return $class;
}


function getImage($host) 
{
global $config;
$sql = "SELECT * FROM `devices` WHERE `device_id` = '$host'";
$data = mysql_fetch_array(mysql_query($sql));
$type = strtolower($data['os']);
  if(file_exists($config['html_dir'] . "/images/os/$type" . ".png")){ $image = "<img src='".$config['base_url']."/images/os/$type.png' />";
  } elseif(file_exists($config['html_dir'] . "/images/os/$type" . ".gif")){ $image = "<img src='".$config['base_url']."/images/os/$type.gif' />"; }
  if($type == "linux") {
    $features = strtolower(trim($data['features']));
    list($distro) = split(" ", $features);
    if(file_exists($config['html_dir'] . "/images/os/$distro" . ".png")){ $image = "<img src='".$config['base_url']."/images/os/$distro" . ".png' />";
    } elseif(file_exists($config['html_dir'] . "/images/os/$distro" . ".gif")){ $image = "<img src='".$config['base_url']."/images/os/$distro" . ".gif' />"; }
  }
  return $image;
}


function renamehost($id, $new) {
  global $config;
  $host = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '$id'"), 0);
  shell_exec("mv ".$config['rrd_dir']."/$host ".$config['rrd_dir']."/$new");
  mysql_query("UPDATE devices SET hostname = '$new' WHERE device_id = '$id'");
  eventlog("Hostname changed -> $new (console)", $id);
}

function delHost($id) 
{
  global $config;
  $host = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '$id'"), 0);
  mysql_query("DELETE FROM `devices` WHERE `device_id` = '$id'");
  $int_query = mysql_query("SELECT * FROM `interfaces` WHERE `device_id` = '$id'");
  while($int_data = mysql_fetch_array($int_query)) {
    $int_if = $int_data['ifDescr'];
    $int_id = $int_data['interface_id'];
    mysql_query("DELETE from `adjacencies` WHERE `interface_id` = '$int_id'");
    mysql_query("DELETE from `links` WHERE `src_if` = '$int_id'");
    mysql_query("DELETE from `links` WHERE `dst_if` = '$int_id'");
    mysql_query("DELETE from `ipaddr` WHERE `interface_id` = '$int_id'");
    mysql_query("DELETE from `ip6adjacencies` WHERE `interface_id` = '$int_id'");
    mysql_query("DELETE from `ip6addr` WHERE `interface_id` = '$int_id'");
    mysql_query("DELETE from `mac_accounting` WHERE `interface_id` = '$int_id'");
    mysql_query("DELETE FROM `bill_ports` WHERE `port_id` = '$int_id'");
    mysql_query("DELETE from `pseudowires` WHERE `interface_id` = '$int_id'");
    echo("Removed interface $int_id ($int_if)<br />");
  }
  mysql_query("DELETE FROM `entPhysical` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `devices_attribs` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `devices_perms` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `bgpPeers` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `temperature` WHERE `temp_host` = '$id'");
  mysql_query("DELETE FROM `vlans` WHERE `device_id` = '$id'");  
  mysql_query("DELETE FROM `vrfs` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `storage` WHERE `host_id` = '$id'");
  mysql_query("DELETE FROM `alerts` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `eventlog` WHERE `host` = '$id'");
  mysql_query("DELETE FROM `syslog` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `interfaces` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `services` WHERE `service_host` = '$id'");
  mysql_query("DELETE FROM `alerts` WHERE `device_id` = '$id'");
  shell_exec("rm -rf ".$config['rrd_dir']."/$host");
  echo("Removed device $host<br />");
}

function retireHost($id)
{
  global $config;
  $host = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '$id'"), 0);
  mysql_query("DELETE FROM `devices` WHERE `device_id` = '$id'");
  $int_query = mysql_query("SELECT * FROM `interfaces` WHERE `device_id` = '$id'");
  while($int_data = mysql_fetch_array($int_query)) {
    $int_if = $int_data['ifDescr'];
    $int_id = $int_data['interface_id'];
    mysql_query("DELETE from `adjacencies` WHERE `interface_id` = '$int_id'");
    mysql_query("DELETE from `links` WHERE `src_if` = '$int_id'");
    mysql_query("DELETE from `links` WHERE `dst_if` = '$int_id'");
    mysql_query("DELETE from `ipaddr` WHERE `interface_id` = '$int_id'");
    mysql_query("DELETE from `ip6adjacencies` WHERE `interface_id` = '$int_id'");
    mysql_query("DELETE from `ip6addr` WHERE `interface_id` = '$int_id'");
    mysql_query("DELETE from `pseudowires` WHERE `interface_id` = '$int_id'");
    echo("Removed interface $int_id ($int_if)<br />");
  }
  mysql_query("DELETE FROM `entPhysical` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `temperature` WHERE `temp_host` = '$id'");
  mysql_query("DELETE FROM `storage` WHERE `host_id` = '$id'");
  mysql_query("DELETE FROM `alerts` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `services` WHERE `service_host` = '$id'");
  shell_exec("rm -rf ".$config['rrd_dir']."/$host");
  echo("Removed device $host<br />");
}

function addHost($host, $community, $snmpver, $port = 161) 
{
  global $config;
  list($hostshort)      = explode(".", $host);
  if ( isDomainResolves($host)){
    if ( isPingable($host)) {
      if ( mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `hostname` = '$host'"), 0) == '0' ) {
        $snmphost = shell_exec($config['snmpget'] ." -m SNMPv2-MIB -Oqv -$snmpver -c $community $host:$port sysName.0");
        if ($snmphost == $host || $hostshort = $host) {
          createHost ($host, $community, $snmpver, $port);
        } else { echo("Given hostname does not match SNMP-read hostname!\n"); }
      } else { echo("Already got host $host\n"); }
    } else { echo("Could not ping $host\n"); }
  } else { echo("Could not resolve $host\n"); }
}

function overlibprint($text) {
	return "onmouseover=\"return overlib('" . $text . "');\" onmouseout=\"return nd();\"";
}

function scanUDP ($host, $port, $timeout) 
{ 
  $handle = fsockopen($host, $port, &$errno, &$errstr, 2); 
  if (!$handle) { 
  } 
  socket_set_timeout ($handle, $timeout); 
  $write = fwrite($handle,"\x00"); 
  if (!$write) { next; } 
  $startTime = time(); 
  $header = fread($handle, 1); 
  $endTime = time(); 
  $timeDiff = $endTime - $startTime;  
  if ($timeDiff >= $timeout) { 
    fclose($handle); return 1; 
  } else { fclose($handle); return 0; } 
}

function humanmedia($media) 
{
	array_preg_replace($rewrite_iftype, $media);	
        return $media;
}

function humanspeed($speed) 
{
	$speed = formatRates($speed);
        if($speed == "") { $speed = "-"; }
	return $speed;
}

function netmask2cidr($netmask) 
{
 list ($network, $cidr) = explode("/", trim(`ipcalc $address/$mask | grep Network | cut -d" " -f 4`));
 return $cidr;
}

function cidr2netmask() 
{
   return (long2ip(ip2long("255.255.255.255")
           << (32-$netmask)));
}

function formatUptime($diff, $format="long") 
{
  $yearsDiff = floor($diff/31536000);
  $diff -= $yearsDiff*31536000;
  $daysDiff = floor($diff/86400);
  $diff -= $daysDiff*86400;
  $hrsDiff = floor($diff/60/60);
  $diff -= $hrsDiff*60*60;
  $minsDiff = floor($diff/60);
  $diff -= $minsDiff*60;
  $secsDiff = $diff;
  
  $uptime = "";
  
  if($format == "short") {
    if($yearsDiff > '0'){ $uptime .= $yearsDiff . "y "; }
    if($daysDiff > '0'){ $uptime .= $daysDiff . "d "; }
    if($hrsDiff > '0'){ $uptime .= $hrsDiff . "h "; }
    if($minsDiff > '0'){ $uptime .= $minsDiff . "m "; }
    if($secsDiff > '0'){ $uptime .= $secsDiff . "s "; }
  } else {
    if($yearsDiff > '0'){ $uptime .= $yearsDiff . " years, "; }
    if($daysDiff > '0'){ $uptime .= $daysDiff   . " days, "; }
    if($hrsDiff > '0'){ $uptime .= $hrsDiff     . "h "; }
    if($minsDiff > '0'){ $uptime .= $minsDiff   . "m "; }
    if($secsDiff > '0'){ $uptime .= $secsDiff   . "s "; }
  }
  return trim($uptime);
}

function isSNMPable($hostname, $community, $snmpver, $port) 
{
     global $config;
     $pos = shell_exec($config['snmpget'] ." -m SNMPv2-MIB -$snmpver -c $community -t 1 $hostname:$port sysDescr.0");
     if($pos == '') {
       $status='0';
     } else {
       $status='1';
     }
     return $status;
}

function isPingable($hostname) {
   global $config;
   $status = shell_exec($config['fping'] . " $hostname");
   if(strstr($status, "alive")) {
     return TRUE;
   } else {
     return FALSE;
   }
}


function is_odd($number) {  
  return $number & 1; // 0 = even, 1 = odd 
}

function isValidInterface($if) {
      global $config;
      $if = strtolower($if);
      $nullintf = 0;
      foreach($config['bad_if'] as $bi) {
         $pos = strpos($if, $bi);
         if ($pos !== FALSE) {
            $nullintf = 1;
            echo("$if matched $bi \n");
         }
      }
      if (preg_match('/serial[0-9]:/', $if)) { $nullintf = '1'; }
      if ($nullintf != '1') {
         return 1;
      }  else { return 0; }
}

function ifclass($ifOperStatus, $ifAdminStatus) 
{
        $ifclass = "interface-upup";
        if ($ifAdminStatus == "down") { $ifclass = "interface-admindown"; }
        if ($ifAdminStatus == "up" && $ifOperStatus== "down") { $ifclass = "interface-updown"; }
        if ($ifAdminStatus == "up" && $ifOperStatus== "up") { $ifclass = "interface-upup"; }
	return $ifclass;
}

function utime() {
        $time = explode( " ", microtime());
        $usec = (double)$time[0];
        $sec = (double)$time[1];
        return $sec + $usec;
}

function fixIOSFeatures($features)
{
	$features = preg_replace("/^PK9S$/", "IP w/SSH LAN Only", $features);
        $features = str_replace("LANBASEK9", "Lan Base Crypto", $features);
	$features = str_replace("LANBASE", "Lan Base", $features);
	$features = str_replace("ADVENTERPRISEK9", "Advanced Enterprise Crypto", $features);
	$features = str_replace("ADVSECURITYK9", "Advanced Security Crypto", $features);
        $features = str_replace("K91P", "Provider Crypto", $features);
	$features = str_replace("K4P", "Provider Crypto", $features);
        $features = str_replace("ADVIPSERVICESK9", "Adv IP Services Crypto", $features);
        $features = str_replace("ADVIPSERVICES", "Adv IP Services", $features);
        $features = str_replace("IK9P", "IP Plus Crypto", $features);
	$features = str_replace("K9O3SY7", "IP ADSL FW IDS Plus IPSEC 3DES", $features);
        $features = str_replace("SPSERVICESK9", "SP Services Crypto", $features);
        $features = preg_replace("/^PK9SV$/", "IP MPLS/IPV6 W/SSH + BGP", $features);
        $features = str_replace("IS", "IP Plus", $features);
        $features = str_replace("IPSERVICESK9", "IP Services Crypto", $features);
        $features = str_replace("BROADBAND", "Broadband", $features);
        $features = str_replace("IPBASE", "IP Base", $features);
        $features = str_replace("IPSERVICE", "IP Services", $features);
        $features = preg_replace("/^P$/", "Service Provider", $features);
	$features = preg_replace("/^P11$/", "Broadband Router", $features);
	$features = preg_replace("/^G4P5$/", "NRP", $features);
        $features = str_replace("JK9S", "Enterprise Plus Crypto", $features);
        $features = str_replace("IK9S", "IP Plus Crypto", $features);
        $features = preg_replace("/^JK$/", "Enterprise Plus", $features);
	$features = str_replace("I6Q4L2", "Layer 2", $features);
        $features = str_replace("I6K2L2Q4", "Layer 2 Crypto", $features);
	$features = str_replace("C3H2S", "Layer 2 SI/EI", $features);
	$features = str_replace("_WAN", " + WAN", $features);
	return $features;
}

function fixIOSHardware($hardware){

        $hardware = preg_replace("/C([0-9]+)/", "Cisco \\1", $hardware);
	$hardware = preg_replace("/CISCO([0-9]+)/", "Cisco \\1", $hardware);
        $hardware = str_replace("cat4000","Cisco Catalyst 4000", $hardware);
        $hardware = str_replace("s3223_rp","Cisco Catalyst 6500 SUP32", $hardware);
        $hardware = str_replace("s222_rp","Cisco Catalyst 6500 SUP2", $hardware);
        $hardware = str_replace("c6sup2_rp","Cisco Catalyst 6500 SUP2", $hardware);
        $hardware = str_replace("s72033_rp","Cisco Catalyst 6500 SUP720 ", $hardware);
        $hardware = str_replace("RSP","Cisco 7500", $hardware);
	$hardware = str_replace("C3200XL", "Cisco Catalyst 3200XL", $hardware);
	$hardware = str_replace("C3550", "Cisco Catalyst 3550", $hardware);
	$hardware = str_replace("C2950", "Cisco Catalyst 2950", $hardware);
	$hardware = str_replace("C7301", "Cisco 7301", $hardware);
        $hardware = str_replace("CE500", "Catalyst Express 500", $hardware);
	return $hardware;

}

function createHost ($host, $community, $snmpver, $port = 161){
        $host = trim(strtolower($host));
        $device = array('hostname' => $host, 'community' => $community, 'snmpver' => $snmpver, 'port' => $port);
        $host_os = getHostOS($device); 
        if($host_os) {
           $sql = mysql_query("INSERT INTO `devices` (`hostname`, `sysName`, `community`, `port`, `os`, `status`,`snmpver`) VALUES ('$host', '$host', '$community', '$port', '$host_os', '1','$snmpver')");
           if(mysql_affected_rows()) {
	     $device_id = mysql_result(mysql_query("SELECT device_id FROM devices WHERE hostname = '$host'"),0);
	     mysql_query("INSERT INTO devices_attribs (attrib_type, attrib_value, device_id) VALUES ('discover','1','$device_id')");
             return("Created host : $host (id:$device_id) (os:$host_os)");
           } else { return FALSE; }
        } else {
	   return FALSE;
	}
}

function isDomainResolves($domain){
     return gethostbyname($domain) != $domain;
}

function hoststatus($id) {
    $sql = mysql_query("SELECT `status` FROM `devices` WHERE `device_id` = '$id'");
    $result = @mysql_result($sql, 0);
    return $result;
}

function match_network ($nets, $ip, $first=false) {
   $return = false;
   if (!is_array ($nets)) $nets = array ($nets);
   foreach ($nets as $net) {
       $rev = (preg_match ("/^\!/", $net)) ? true : false;
       $net = preg_replace ("/^\!/", "", $net);
       $ip_arr  = explode('/', $net);
       $net_long = ip2long($ip_arr[0]);
       $x        = ip2long($ip_arr[1]);
       $mask    = long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
       $ip_long  = ip2long($ip);
       if ($rev) {
           if (($ip_long & $mask) == ($net_long & $mask)) return false;
       } else {
           if (($ip_long & $mask) == ($net_long & $mask)) $return = true;
           if ($first && $return) return true;
       }
   }
   return $return;
}

function snmp2ipv6($ipv6_snmp)
{
  $ipv6 = explode('.',$ipv6_snmp);
  for ($i = 0;$i <= 15;$i++) { $ipv6[$i] = zeropad(dechex($ipv6[$i])); }
  for ($i = 0;$i <= 15;$i+=2) { $ipv6_2[] = $ipv6[$i] . $ipv6[$i+1]; }
  return implode(':',$ipv6_2);
}

function ipv62snmp($ipv6)
{
  $ipv6_ex = explode(':',Net_IPv6::uncompress($ipv6));
  for ($i = 0;$i < 8;$i++) { $ipv6_ex[$i] = zeropad_lineno($ipv6_ex[$i],4); }
  $ipv6_ip = implode('',$ipv6_ex);
  for ($i = 0;$i < 32;$i+=2) $ipv6_split[] = hexdec(substr($ipv6_ip,$i,2));
  return implode('.',$ipv6_split);
}

function discover_process_ipv6($ifIndex,$ipv6_address,$ipv6_prefixlen,$ipv6_origin)
{
  global $valid_v6,$device,$config;

  $ipv6_network   = trim(shell_exec($config['sipcalc']." $ipv6_address/$ipv6_prefixlen | grep Subnet | cut -f 2 -d '-'"));
  $ipv6_compressed = trim(shell_exec($config['sipcalc']." $ipv6_address/$ipv6_prefixlen | grep Compressed | cut -f 2 -d '-'"));
  
  $ipv6_type = trim(shell_exec($config['sipcalc']." $ipv6_address/$ipv6_prefixlen | grep \"Address type\" | cut -f 2- -d '-'"));
  
  if ($ipv6_type == "Link-Local Unicast Addresses") return; # ignore link-locals (coming from IPV6-MIB)

  if (mysql_result(mysql_query("SELECT count(*) FROM `interfaces`
        WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) != '0' && $ipv6_prefixlen > '0' && $ipv6_prefixlen < '129' && $ipv6_compressed != '::1') {
    $i_query = "SELECT interface_id FROM `interfaces` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'";
    $interface_id = mysql_result(mysql_query($i_query), 0);
    if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ipv6_networks` WHERE `ipv6_network` = '$ipv6_network'"), 0) < '1') {
      mysql_query("INSERT INTO `ipv6_networks` (`ipv6_network`) VALUES ('$ipv6_network')");
      echo("N");
    }

    if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ipv6_networks` WHERE `ipv6_network` = '$ipv6_network'"), 0) < '1') {
      mysql_query("INSERT INTO `ipv6_networks` (`ipv6_network`) VALUES ('$ipv6_network')");
      echo("N");
    }

    $ipv6_network_id = @mysql_result(mysql_query("SELECT `ipv6_network_id` from `ipv6_networks` WHERE `ipv6_network` = '$ipv6_network'"), 0);

    if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ipv6_addresses` WHERE `ipv6_address` = '$ipv6_address' AND `ipv6_prefixlen` = '$ipv6_prefixlen' AND `interface_id` = '$interface_id'"), 0) == '0') {
     mysql_query("INSERT INTO `ipv6_addresses` (`ipv6_address`, `ipv6_compressed`, `ipv6_prefixlen`, `ipv6_origin`, `ipv6_network_id`, `interface_id`)
                                   VALUES ('$ipv6_address', '$ipv6_compressed', '$ipv6_prefixlen', '$ipv6_origin', '$ipv6_network_id', '$interface_id')");
     echo("+");
    } else { echo("."); }
    $full_address = "$ipv6_address/$ipv6_prefixlen";
    $valid = $full_address  . "-" . $interface_id;
    $valid_v6[$valid] = 1;
  }
}

function get_astext($asn)
{
  global $config,$cache;

  if (isset($config['astext'][$asn]))
  {
    return $config['astext'][$asn];
  }
  else
  {
    if (isset($cache['astext'][$asn]))
    {
      return $cache['astext'][$asn];
    }
    else
    {
      $result = dns_get_record("AS$asn.asn.cymru.com",DNS_TXT);
      $txt = explode('|',$result[0]['txt']);
      $cache['astext'][$asn] = $txt[4];
      return trim(str_replace('"', '', $txt[4]));
    }
  }
}

function eventlog($eventtext,$device_id = "", $interface_id = "")
{
  $event_query = "INSERT INTO eventlog (host, interface, datetime, message) VALUES (" . ($device_id ? $device_id : "NULL");
  $event_query .= ", " . ($interface_id ? $interface_id : "NULL") . ", NOW(), '" . mysql_escape_string($eventtext) . "')";
  mysql_query($event_query);
}
                                                                                                                                
?>
