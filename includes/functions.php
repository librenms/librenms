<?

## Include from PEAR

include_once("Net/IPv4.php");
include_once("Net/IPv6.php");

## Observer Includes

include_once($config['install_dir'] . "/includes/generic.php");
include_once($config['install_dir'] . "/includes/ios.php");
include_once($config['install_dir'] . "/includes/unix.php");
include_once($config['install_dir'] . "/includes/procurve.php");
include_once($config['install_dir'] . "/includes/graphing.php");
include_once($config['install_dir'] . "/includes/print-functions.php");
include_once($config['install_dir'] . "/includes/billing-functions.php");
include_once($config['install_dir'] . "/includes/cisco-entities.php");
include_once($config['install_dir'] . "/includes/syslog.php");

function mres($string) {
 // short function wrapper because the real one is stupidly long and ugly. aestetics.
 return mysql_real_escape_string($string);
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
  return shell_exec($config['rrdtool'] . " update $rrdfile $rrdupdate");
}

function getHostOS($hostname, $community, $snmpver) {

    global $config;

    $sysDescr_cmd = $config['snmpget']." -O qv -" . $snmpver . " -c " . $community . " " . $hostname . " sysDescr.0";
    $sysDescr = str_replace("\"", "", trim(shell_exec($sysDescr_cmd)));
    $dir_handle = @opendir($config['install_dir'] . "/includes/osdiscovery") or die("Unable to open $path");
    while ($file = readdir($dir_handle)) {
      if( preg_match("/^discover-([a-z0-9]*).php/", $file) ) {
        include($config['install_dir'] . "includes/osdiscovery/" . $file);
      }
    }
    closedir($dir_handle);
    if($os) { return $os; } else { return FALSE; }

}


function strgen ($length = 16)
{
    $entropy = array(0,1,2,3,4,5,6,7,8,9,'a','A','b','B','c','C','d','D','e',
    'E','f','F','g','G','h','H','i','I','j','J','k','K','l','L','m','M','n',
    'N','o','O','p','P','q','Q','r','R','s','S','t','T','u','U','v','V','w',
    'W','x','X','y','Y','z','Z');
    
    $string = "";
    
    for ($i=0; $i<$length; $i++) {
        $key = mt_rand(0,61);
        $string .= $entropy[$key];
    }
    
    return $string;
}

function billpermitted($bill_id) {

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
  } elseif ( devicepermitted(mysql_result(mysql_query("SELECT device_id FROM interfaces WHERE interface_id = '$interface_id'"),0))) {
    $allowed = TRUE;
  } elseif ( @mysql_result(mysql_query("SELECT interface_id FROM interfaces_perms WHERE `user_id` = '" . $_SESSION['user_id'] . "' AND `interface_id` = $interface_id"), 0)) {
    $allowed = TRUE;
  } else { $allowed = FALSE; }
  return $allowed;
}

function devicepermitted($device_id) 
{
  global $_SESSION;
  if($_SESSION['userlevel'] >= "5") { $allowed = true; 
  } elseif ( @mysql_result(mysql_query("SELECT * FROM devices_perms WHERE `user_id` = '" . $_SESSION['user_id'] . "' AND `device_id` = $device_id"), 0) > '0' ) {
    $allowed = true;
  } else { $allowed = false; }
  return $allowed;

}

function formatRates($rate) 
{
   $rate = format_si($rate) . "bps";
   return $rate;
}

function formatstorage($rate) 
{
   $rate = format_bi($rate) . "B";
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

function format_bi($size) {
  $sizes = Array('', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei');
  $ext = $sizes[0];
  for ($i=1; (($i < count($sizes)) && ($size >= 1024)); $i++) { $size = $size / 1024; $ext  = $sizes[$i];  }
  return round($size, 2).$ext;
}

function arguments($argv) {
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

function percent_colour_old($perc) {
  $red = round(5 * $perc);
  $blue = round(255 - (5 * $perc));
  if($red > '255') { $red = "255"; } 
  if($blue < '0') { $blue = "0"; }
  $red = dechex($red);
  $blue = dechex($blue);
  if(strlen($red) == 1) { $red = "0$red"; }
  if(strlen($blue) == 1) { $blue = "0$blue"; }
  $colour = "#$red" . "00" . "$blue";
  return $colour;
}

function print_error($text){
  echo("<table class=errorbox cellpadding=3><tr><td><img src='/images/15/exclamation.png' align=absmiddle> $text</td></tr></table>");
}

function print_message($text){
  echo("<table class=messagebox cellpadding=3><tr><td><img src='/images/16/tick.png' align=absmiddle> $text</td></tr></table>");
}

function truncate($substring, $max = 50, $rep = '...') {
  if(strlen($substring) < 1){ $string = $rep; } else { $string = $substring; }
  $leave = $max - strlen ($rep);      
  if(strlen($string) > $max){ return substr_replace($string, $rep, $leave); } else { return $string; }      
}


function interface_rates ($interface) {
  global $config, $rrd_dir;
  $rrdfile = $rrd_dir . "/" . $interface['hostname'] . "/" . $interface['ifIndex'] . ".rrd";
  $cmd  = $config['rrdtool']." fetch -s -600s -e now ".$rrdfile." AVERAGE | grep : | cut -d\" \" -f 2,3 | grep e";
  $data = trim(`$cmd`);
  foreach( explode("\n", $data) as $entry) {
    list($in, $out) = split(" ", $entry);
    $rate['in'] = $in * 8;
    $rate['out'] = $out * 8;
  }
  return $rate;
}


function interface_errors ($interface) {
  global $config, $rrd_dir;
  $rrdfile = $rrd_dir . "/" . $interface['hostname'] . "/" . $interface['ifIndex'] . ".rrd";
  $cmd = $config['rrdtool']." fetch -s -1d -e -300s $rrdfile AVERAGE | grep : | cut -d\" \" -f 4,5";
  $data = trim(`$cmd`);
  foreach( explode("\n", $data) as $entry) {
        list($in, $out) = explode(" ", $entry);
        $in_errors += ($in * 300);
        $out_errors += ($out * 300);
  }
  $errors['in'] = round($in_errors);
  $errors['out'] = round($out_errors);
  return $errors;
}


function geteventicon ($message) {
  if($message == "Device status changed to Down") { $icon = "server_connect.png"; }
  if($message == "Device status changed to Up") { $icon = "server_go.png"; }
  if($message == "Interface went down" || $message == "Interface changed state to Down" ) { $icon = "if-disconnect.png"; }
  if($message == "Interface went up" || $message == "Interface changed state to Up" ) { $icon = "if-connect.png"; }
  if($message == "Interface disabled") { $icon = "if-disable.png"; }
  if($message == "Interface enabled") { $icon = "if-enable.png"; }
  if($icon) { return $icon; } else { return false; }
}

function generateiflink($interface, $text=0,$type=bits) {
  global $twoday; global $now; global $config; global $day;
  if(!$text) { $text = fixIfName($interface['ifDescr']); }
  if(!$type) { $type = 'bits'; }
  $class = ifclass($interface['ifOperStatus'], $interface['ifAdminStatus']);
  $graph_url = "graph.php?if=" . $interface['interface_id'] . "&from=$day&to=$now&width=400&height=120&type=" . $type;
  $link  = "<a class=$class href='?page=interface&id=" . $interface['interface_id'] . "'  ";
  $link .= "onmouseover=\"return overlib('<div class=list-large>" . $interface['hostname'] . " - " . $interface['ifDescr'] . "</div><div>";
  $link .= $interface['ifAlias'] . "</div><img src=\'$graph_url\'>'".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">$text</a>";
  return $link;
}

function generatedevicelink($device, $text=0, $start=0, $end=0) {
  global $twoday; global $day; global $now; global $config;
  if(!$start) { $start = $day; }
  if(!$end) { $end = $now; }
  $class = devclass($device);
  if(!$text) { $text = $device['hostname']; }
  $graph_url = "graph.php?host=" . $device['device_id'] . "&from=$start&to=$end&width=400&height=120&type=cpu";
  $link  = "<a class=$class href='?page=device&id=" . $device['device_id'] . "' ";
  $link .= "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - CPU Load</div>";
  $link .= "<img src=\'$graph_url\'>'".$config['overlib_defaults'].", LEFT);\" onmouseout=\"return nd();\">$text</a>";
  return $link;
}


function device_traffic_image($device, $width, $height, $from, $to) {
  return "<img src='graph.php?device=" . $device . "&type=device_bits&from=" . $from . "&to=" . $to . "&width=" . $width . "&height=" . $height . "' />";
}

function devclass($device) {
   if ($device['status'] == '0') { $class = "list-device-down"; } else { $class = "list-device"; }
   if ($device['ignore'] == '1') {
     $class = "list-device-ignored";
     if ($device['status'] == '1') { $class = "list-device-ignored-up"; }
   }
  return $class;
}


function getImage($host) {
$sql = "SELECT * FROM `devices` WHERE `device_id` = '$host'";
$data = mysql_fetch_array(mysql_query($sql));
$type = strtolower($data['os']);
  if(file_exists("images/os/$type" . ".png")){ $image = "<img src='images/os/$type.png'>";
  } elseif(file_exists("images/os/$type" . ".gif")){ $image = "<img src='images/os/$type.gif'>"; }
  if($device['monowall']) {$image = "<img src='images/os/m0n0wall.png'>";}
  if($type == "linux") {
    $features = strtolower(trim($data[features]));
    list($distro) = split(" ", $features);
    if(file_exists("images/os/$distro" . ".png")){ $image = "<img src='images/os/$distro" . ".png'>";
    } elseif(file_exists("images/os/$distro" . ".gif")){ $image = "<img src='images/os/$distro" . ".gif'>"; }
  }
  return $image;
}


function renamehost($id, $new) {
  global $config;
  $host = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '$id'"), 0);
  shell_exec("mv ".$config['rrd_dir']."/$host ".$config['rrd_dir']."/$new");
  mysql_query("UPDATE devices SET hostname = '$new' WHERE device_id = '$id'");
    mysql_query("INSERT INTO eventlog (host, datetime, message) VALUES ('" . $id . "', NULL, NOW(), 'Hostname changed -> $new (console)')");
}

function delHost($id) {
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
    echo("Removed interface $int_id ($int_if)<br />");
  }
  mysql_query("DELETE FROM `devices_attribs` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `temperature` WHERE `temp_host` = '$id'");
  mysql_query("DELETE FROM `storage` WHERE `host_id` = '$id'");
  mysql_query("DELETE FROM `alerts` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `eventlog` WHERE `host` = '$id'");
  mysql_query("DELETE FROM `syslog` WHERE `host` = '$id'");
  mysql_query("DELETE FROM `interfaces` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `services` WHERE `service_host` = '$id'");
  mysql_query("DELETE FROM `alerts` WHERE `device_id` = '$id'");
  $rrd_dir = $config['rrd_dir'];
  `rm -f $rrd_dir/$host-*.rrd`;
  `rm -rf $rrd_dir/$host`;
  echo("Removed device $host<br />");
}


function addHost($host, $community, $snmpver) {
  global $config;
  list($hostshort)      = explode(".", $host);
  if ( isDomainResolves($host)){
    if ( isPingable($host)) {
      if ( mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `hostname` = '$host'"), 0) == '0' ) {
        $snmphost = shell_exec($config['snmpget'] ." -Oqv -$snmpver -c $community $host sysName.0");
        if ($snmphost == $host || $hostshort = $host) {
          createHost ($host, $community, $snmpver);
        } else { echo("Given hostname does not match SNMP-read hostname!\n"); }
      } else { echo("Already got host $host\n"); }
    } else { echo("Could not ping $host\n"); }
  } else { echo("Could not resolve $host\n"); }
}

function overlibprint($text) {
	return "onmouseover=\"return overlib('" . $text . "');\" onmouseout=\"return nd();\"";
}

function scanUDP ($host, $port, $timeout) { 
  $handle = fsockopen($host, $port, &$errno, &$errstr, 2); 
  if (!$handle) { 
  } 
  socket_set_timeout ($handle, $timeout); 
  $write = fwrite($handle,"\x00"); 
  if (!$write) { 
    next; 
  } 
  $startTime = time(); 
  $header = fread($handle, 1); 
  $endTime = time(); 
  $timeDiff = $endTime - $startTime;  
  if ($timeDiff >= $timeout) { 
    fclose($handle); 
    return 1; 
  } else { 
    fclose($handle); 
    return 0; 
  } 
}

function humanmedia($media) {
        $media = preg_replace("/^ethernetCsmacd$/", "Ethernet", $media);
        $media = preg_replace("/^softwareLoopback$/", "Loopback", $media);
        $media = preg_replace("/^tunnel$/", "Tunnel", $media);
	$media = preg_replace("/^propVirtual$/", "Virtual Int", $media);
	$media = preg_replace("/^ppp$/", "PPP", $media);
	$media = preg_replace("/^ds1$/", "DS1", $media);
	$media = preg_replace("/^pos$/", "POS", $media);
	$media = preg_replace("/^sonet$/", "SONET", $media);
	$media = preg_replace("/^slip$/", "SLIP", $media);
	$media = preg_replace("/^mpls$/", "MPLS Layer", $media);
	$media = preg_replace("/^l2vlan$/", "VLAN Subif", $media);
	$media = preg_replace("/^atm$/", "ATM", $media);
	$media = preg_replace("/^aal5$/", "ATM AAL5", $media);
	$media = preg_replace("/^atmSubInterface$/", "ATM Subif", $media);
        $media = preg_replace("/^propPointToPointSerial$/", "PtP Serial", $media);

        return $media;
}


function humanspeed($speed) {
#        $speed = preg_replace("/^0$/", "-", $speed);
#        $speed = preg_replace("/^9000$/", "9Kbps", $speed);
#	$speed = preg_replace("/^48000$/", "48Kbps", $speed);
#	$speed = preg_replace("/^56000$/", "56Kbps", $speed);
#        $speed = preg_replace("/^64000$/", "64Kbps", $speed);
#        $speed = preg_replace("/^128000$/", "128Kbps", $speed);
#        $speed = preg_replace("/^256000$/", "256Kbps", $speed);
#	$speed = preg_replace("/^448000$/", "448Kbps", $speed);
#        $speed = preg_replace("/^512000$/", "512Kbps", $speed);
#	$speed = preg_replace("/^768000$/", "768Kbps", $speed);
#        $speed = preg_replace("/^1024000$/", "1Mbps", $speed);
#        $speed = preg_replace("/^2048000$/", "2Mbps", $speed);
#        $speed = preg_replace("/^4192000$/", "4Mbps", $speed);
#	$speed = preg_replace("/^10000000$/", "10Mbps", $speed);
#	$speed = preg_replace("/^34000000$/", "34Mbps", $speed);
#        $speed = preg_replace("/^45000000$/", "45Mbps", $speed);
#        $speed = preg_replace("/^54000000$/", "54Mbps", $speed);
#        $speed = preg_replace("/^100000000$/", "100Mbps", $speed);
# 	$speed = preg_replace("/^155000000$/", "155Mbps", $speed);
#        $speed = preg_replace("/^622000000$/", "622Mbps", $speed);
#        $speed = preg_replace("/^1000000000$/", "1Gbps", $speed);
#        $speed = preg_replace("/^10000000000$/", "10Gbps", $speed);
#	$speed = preg_replace("/^4294967295$/", "", $speed);
	$speed = formatRates($speed);
        if($speed == "") { $speed = "-"; }
	return $speed;
}


function netmask2cidr($netmask) {
 list ($network, $cidr) = explode("/", trim(`ipcalc $address/$mask | grep Network | cut -d" " -f 4`));
 return $cidr;
}

function cidr2netmask() {
   return (long2ip(ip2long("255.255.255.255")
           << (32-$netmask)));
}

function formatUptime($diff, $format="long") {
  $yearsDiff = floor($diff/31536000);
  $diff -= $yearsDiff*31536000;
  $daysDiff = floor($diff/86400);
  $diff -= $daysDiff*86400;
  $hrsDiff = floor($diff/60/60);
  $diff -= $hrsDiff*60*60;
  $minsDiff = floor($diff/60);
  $diff -= $minsDiff*60;
  $secsDiff = $diff;
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
  return "$uptime";
}

function isSNMPable($hostname, $community, $snmpver) {
     global $config;
     $pos = shell_exec($config['snmpget'] ." -$snmpver -c $community -t 1 $hostname sysDescr.0");
     if($pos == '') {
       $status='0';
       $posb = shell_exec($config['snmpget'] ." -$snmpver -c $community -t 1 $hostname 1.3.6.1.2.1.7526.2.4");
       if($posb == '') { } else { $status='1'; }
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

function ifclass($ifOperStatus, $ifAdminStatus) {
        $ifclass = "interface-upup";
        if ($ifAdminStatus == "down") { $ifclass = "interface-admindown"; }
        if ($ifAdminStatus == "up" && $ifOperStatus== "down") { $ifclass = "interface-updown"; }
        if ($ifAdminStatus == "up" && $ifOperStatus== "up") { $ifclass = "interface-upup"; }
	return $ifclass;
}

function makeshortif($if) {
	$if = strtolower($if);
	$if = str_replace("tengigabitethernet","Te", $if);
	$if = str_replace("gigabitethernet","Gi", $if);
	$if = str_replace("fastethernet","Fa", $if);
	$if = str_replace("ethernet","Et", $if);
        $if = str_replace("serial","Se", $if);
        $if = str_replace("pos","Pos", $if);
	$if = str_replace("port-channel","Po", $if);
        $if = str_replace("atm","Atm", $if);
	$if = str_replace("null", "Null", $if);
	$if = str_replace("loopback","Lo", $if);        
	$if = str_replace("dialer","Di", $if);
	$if = str_replace("vlan","Vlan", $if);
        $if = str_replace("tunnel","Tunnel", $if);
	return $if;
}

function utime() {
        $time = explode( " ", microtime());
        $usec = (double)$time[0];
        $sec = (double)$time[1];
        return $sec + $usec;
}

function fixiftype ($type) {
	$type = str_replace("ethernetCsmacd", "Ethernet", $type);
        $type = str_replace("tunnel", "Tunnel", $type);
        $type = str_replace("softwareLoopback", "Software Loopback", $type);
        $type = str_replace("propVirtual", "Ethernet VLAN", $type);
        $type = str_replace("ethernetCsmacd", "Ethernet", $type);
        $type = str_replace("l2vlan", "Ethernet VLAN", $type);       
	$type = str_replace("frameRelay", "Frame Relay", $type);
	$type = str_replace("propPointToPointSerial", "PointToPoint Serial", $type);
	return ($type);
}

function fixifName ($inf) {
        $inf = str_replace("ether", "Ether", $inf);
        $inf = str_replace("gig", "Gig", $inf);
        $inf = str_replace("fast", "Fast", $inf);
        $inf = str_replace("ten", "Ten", $inf);
	$inf = str_replace("bvi", "BVI", $inf);
        $inf = str_replace("vlan", "Vlan", $inf);
        $inf = str_replace("ether", "Ether", $inf);
        $inf = str_replace("-802.1q Vlan subif", "", $inf);
	$inf = str_replace("-802.1q", "", $inf);
	$inf = str_replace("tunnel", "Tunnel", $inf);
        $inf = str_replace("serial", "Serial", $inf);
        $inf = str_replace("-aal5 layer", " aal5", $inf);
	$inf = str_replace("null", "Null", $inf);
        $inf = str_replace("atm", "ATM", $inf);
        $inf = str_replace("port-channel", "Port-Channel", $inf);
        $inf = str_replace("dial", "Dial", $inf);
        $inf = str_replace("hp procurve switch software loopback interface", "Loopback Interface", $inf);
        $inf = str_replace("control plane interface", "Control Plane", $inf);
        $inf = str_replace("loop", "Loop", $inf);
        $inf = preg_replace("/^([0-9]+)$/", "Interface \\0", $inf);
	return $inf;
}

function fixIOSFeatures($features){
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

function createHost ($host, $community, $snmpver){
        $host = trim(strtolower($host));
        $host_os = getHostOS($host, $community, $snmpver); 
        if($host_os) {
           $sql = mysql_query("INSERT INTO `devices` (`hostname`, `community`, `os`, `status`) VALUES ('$host', '$community', '$host_os', '1')");
           if(mysql_affected_rows()) {
             return("Created host : $host ($host_os)");
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

function gethostbyid($id) {
     $sql = mysql_query("SELECT `hostname` FROM `devices` WHERE `device_id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function getifhost($id) {
     $sql = mysql_query("SELECT `device_id` from `interfaces` WHERE `interface_id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function getpeerhost($id) {
     $sql = mysql_query("SELECT `device_id` from `bgpPeers` WHERE `bgpPeer_id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function getifindexbyid($id) {
     $sql = mysql_query("SELECT `ifIndex` FROM `interfaces` WHERE `interface_id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function getifbyid($id) {
     $sql = mysql_query("SELECT `ifDescr` FROM `interfaces` WHERE `interface_id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function getidbyname($domain){
     $sql = mysql_query("SELECT `device_id` FROM `devices` WHERE `hostname` = '$domain'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function gethostosbyid($id) {
     $sql = mysql_query("SELECT `os` FROM `devices` WHERE `device_id` = '$id'");
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

?>
