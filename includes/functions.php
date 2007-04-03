<?

include("generic.php");
include("ios.php");
include("unix.php");
include("windows.php");
include("procurve.php");
include("snom.php");
include("graphing.php");

function print_error($text){

echo("<table class=errorbox cellpadding=3><tr><td><img src='/images/15/exclamation.png' align=absmiddle> $text</td></tr></table>");

}

function print_message($text){

echo("<table class=messagebox cellpadding=3><tr><td><img src='/images/16/tick.png' align=absmiddle> $text</td></tr></table>");

}

function truncate($substring, $max = 50, $rep = '...') {
  if(strlen($substring) < 1){
     $string = $rep;
  }else{
    $string = $substring;
  }
      
  $leave = $max - strlen ($rep);
      
  if(strlen($string) > $max){
    return substr_replace($string, $rep, $leave);
  }else{
    return $string;
  }      
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


function generateiflink($iface, $text=0) {
  global $twoday;
  global $now;
  if(!$text) { $text = fixIfName($iface['if']); }
  $class = ifclass($iface['up'], $iface['up_admin']);
  $graph_url = "graph.php?if=$iface[id]&from=$twoday&to=$now&width=400&height=120&type=bits";
  $link = "<a class=$class href='?page=interface&id=$iface[id]'  onmouseover=\"return overlib('<img src=\'$graph_url\'>');\" onmouseout=\"return nd();\">$text</a>";
  return $link;
}

function generatedevicelink($device, $text=0) {
  global $twoday;
  global $now;
  if($device['dev_id']) { $id = $device['dev_id']; } else { $id = $device['id']; }
  $class = devclass($device);
  if(!$text) { $text = $device[hostname]; }
  $graph_url = "graph.php?host=$id&from=$twoday&to=$now&width=400&height=120&type=cpu";
  $link = "<a class=$class href='?page=device&id=$id' onmouseover=\"return overlib('<img src=\'$graph_url\'>');\" onmouseout=\"return nd();\">$text</a>";
  return $link;
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

$sql = "SELECT * FROM `devices` WHERE `id` = '$host'";
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


function delHost($id) {

  $host = mysql_result(mysql_query("SELECT hostname FROM devices WHERE id = '$id'"), 0);
  mysql_query("DELETE FROM `devices` WHERE `id` = '$id'");
  $int_query = mysql_query("SELECT * FROM `interfaces` WHERE `host` = '$id'");
  while($int_data = mysql_fetch_array($int_query)) {
    $int_if = $int_data['if'];
    $int_id = $int_data['id'];
    mysql_query("DELETE from `adjacencies` WHERE `interface_id` = '$int_id'");
    mysql_query("DELETE from `links` WHERE `src_if` = '$int_id'");
    mysql_query("DELETE from `links` WHERE `dst_if` = '$int_id'");
    mysql_query("DELETE from `ipaddr` WHERE `interface_id` = '$int_id'");
    echo("Removed interface $int_id ($int_if)<br />");
  }
  mysql_query("DELETE FROM `storage` WHERE `host_id` = '$id'");
  mysql_query("DELETE FROM `alerts` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `eventlog` WHERE `host` = '$id'");
  mysql_query("DELETE FROM `interfaces` WHERE `host` = '$id'");
  mysql_query("DELETE FROM `services` WHERE `service_host` = '$id'");
  `rm -f rrd/$host-*.rrd`;
  echo("Removed device $host<br />");
}


function addHost($host, $community, $snmpver) {
  list($hostshort)      = explode(".", $host);
  if ( isDomainResolves($host)){
    if ( isPingable($host)) {
      if ( mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `hostname` = '$host'"), 0) == '0' ) {
        $snmphost = trim(`snmpwalk -Oqv -$snmpver -c $community $host sysname | sed s/\"//g`);
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
        $media = preg_replace("/^softwareLoopback$/", "Software Loopback", $media);
        $media = preg_replace("/^tunnel$/", "Tunnel", $media);
	$media = preg_replace("/^propVirtual$/", "Ethernet VLAN", $media);
	$media = preg_replace("/^ppp$/", "PPP", $media);
	$media = preg_replace("/^slip$/", "SLIP", $media);
        return $media;
}


function humanspeed($speed) {
        $speed = preg_replace("/^0$/", "-", $speed);
        $speed = preg_replace("/^9000$/", "9Kbps", $speed);
	$speed = preg_replace("/^48000$/", "48Kbps", $speed);
        $speed = preg_replace("/^64000$/", "64Kbps", $speed);
        $speed = preg_replace("/^128000$/", "128Kbps", $speed);
        $speed = preg_replace("/^256000$/", "256Kbps", $speed);
        $speed = preg_replace("/^512000$/", "512Kbps", $speed);
	$speed = preg_replace("/^768000$/", "768Kbps", $speed);
        $speed = preg_replace("/^1024000$/", "1Mbps", $speed);
        $speed = preg_replace("/^2048000$/", "2Mbps", $speed);
        $speed = preg_replace("/^4192000$/", "4Mbps", $speed);
	$speed = preg_replace("/^10000000$/", "10Mbps", $speed);
	$speed = preg_replace("/^34000000$/", "34Mbps", $speed);
        $speed = preg_replace("/^100000000$/", "100Mbps", $speed);
 	$speed = preg_replace("/^155000000$/", "155Mbps", $speed);
        $speed = preg_replace("/^622000000$/", "622Mbps", $speed);
        $speed = preg_replace("/^1000000000$/", "1Gbps", $speed);
        $speed = preg_replace("/^10000000000$/", "10Gbps", $speed);
	$speed = preg_replace("/^4294967295$/", "", $speed);
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

function formatUptime($diff) {
  $daysDiff = floor($diff/86400);
  $diff -= $daysDiff*86400;
  $hrsDiff = floor($diff/60/60);
  $diff -= $hrsDiff*60*60;
  $minsDiff = floor($diff/60);
  $diff -= $minsDiff*60;
  $secsDiff = $diff;
  if($daysDiff > '0'){ $uptime .= "$daysDiff days, "; }
  if($hrsDiff > '0'){ $uptime .= $hrsDiff . "h "; }
  if($minsDiff > '0'){ $uptime .= $minsDiff . "m "; }
  if($secsDiff > '0'){ $uptime .= $secsDiff . "s "; }
  return "$uptime";
}

function isSNMPable($hostname, $community, $snmpver) {
     $pos = `snmpget -$snmpver -c $community -t 1 $hostname sysDescr.0`;
     if($pos == '') {
       $status='0';
       $posb = `snmpget -$snmpver -c $community -t 1 $hostname 1.3.6.1.2.1.7526.2.4`;
       if($posb == '') { } else { $status='1'; }
     } else {
       $status='1';
     }
     return $status;
}

function isPingable($hostname) {
   global $fping;
   $status = `$fping $hostname | cut -d " " -f 3`;
   $status = trim($status);

   if($status == "alive") {
     return TRUE;
   } else {
     return FALSE;
   }
}


function is_odd($number) {
   return $number & 1; // 0 = even, 1 = odd
}

function isValidInterface($if) {
      $if = strtolower($if);
      $bif = array("null", "virtual-", "unrouted", "eobc", "mpls", "aal5", "-atm layer", "dialer", "-shdsl", "-adsl", "async", "sit0", "sit1");
      $nullintf = 0;
      foreach($bif as $bi) {
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

function ifclass($up, $up_admin) {
        $ifclass = "interface-upup";

        if ($up_admin == "down") { $ifclass = "interface-admindown"; }
        if ($up_admin == "up" && $up == "down") { $ifclass = "interface-updown"; }
        if ($up_admin == "up" && $up == "up") { $ifclass = "interface-upup"; }
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
	$if = str_replace("loopback","Lo", $if);        
	$if = str_replace("dialer","Di", $if);
	$if = str_replace("vlan","Vlan", $if);
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
        
	return ($type);

}

function fixifName ($inf) {
        $inf = str_replace("ether", "Ether", $inf);
        $inf = str_replace("gig", "Gig", $inf);
        $inf = str_replace("fast", "Fast", $inf);
        $inf = str_replace("ten", "Ten", $inf);
        $inf = str_replace("vlan", "Vlan", $inf);
        $inf = str_replace("ether", "Ether", $inf);
        $inf = str_replace("loop", "Loop", $inf);
        $inf = str_replace("-802.1q Vlan subif", "", $inf);
        $inf = str_replace("serial", "Serial", $inf);
        $inf = str_replace("-aal5 layer", " aal5", $inf);
        $inf = str_replace("atm", "ATM", $inf);
        $inf = str_replace("port-channel", "Port-Channel", $inf);
        $inf = str_replace("dial", "Dial", $inf);
        $inf = str_replace("hp procurve switch software Loopback interface", "Loopback Interface", $inf);
        $inf = str_replace("control plane interface", "Control Plane", $inf);
	return $inf;
}

function trafgraph ($rrd, $graph, $from, $to, $width, $height)
{
  global $rrdtool, $installdir, $mono_font;    
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--alt-autoscale-max",
		 "-E", 
                 "--start", $from, "--end", $to, 
                 "--width", $width, "--height", $height, 
                 "DEF:inoctets=$database:INOCTETS:AVERAGE",
                 "DEF:outoctets=$database:OUTOCTETS:AVERAGE",
                 "CDEF:doutoctets=outoctets,-1,*",
                 "CDEF:inbits=inoctets,8,*",
                 "CDEF:outbits=outoctets,8,*",
                 "CDEF:doutbits=doutoctets,8,*",
		 "VDEF:95thin=inbits,95,PERCENT",
		 "VDEF:95thout=outbits,95,PERCENT",
	         "VDEF:d95thout=doutbits,5,PERCENT",
                 "AREA:inbits#CDEB8B:",
                 "COMMENT:BPS    Current   Average      Max   95th %\\n",
                 "LINE1.25:inbits#006600:In ",
                 "GPRINT:inbits:LAST:%6.2lf%s",
                 "GPRINT:inbits:AVERAGE:%6.2lf%s",
                 "GPRINT:inbits:MAX:%6.2lf%s",
                 "GPRINT:95thin:%6.2lf%s\\n",
                 "AREA:doutbits#C3D9FF:",
                 "LINE1.25:doutbits#000099:Out",
                 "GPRINT:outbits:LAST:%6.2lf%s",
                 "GPRINT:outbits:AVERAGE:%6.2lf%s",
                 "GPRINT:outbits:MAX:%6.2lf%s",
                 "GPRINT:95thout:%6.2lf%s",
                 "LINE1:95thin#aa0000",
                 "LINE1:d95thout#aa0000:" );
  

  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);

  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) )
  {
    $err = rrd_error();
  #  echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}

function pktsgraph ($rrd, $graph, $from, $to, $width, $height) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--alt-autoscale-max",
                 "-l 0",
                 "-E", 
                 "--start", $from, "--end", $to,
                 "--width", $width, "--height", $height,
                 "DEF:in=$database:INUCASTPKTS:AVERAGE",
                 "DEF:out=$database:OUTUCASTPKTS:AVERAGE",
                 "CDEF:dout=out,-1,*",
                 "AREA:in#aa66aa:",
                 "COMMENT:Packets    Current     Average      Maximum\\n",
                 "LINE1.25:in#330033:In  ",
                 "GPRINT:in:LAST:%6.2lf%spps",
                 "GPRINT:in:AVERAGE:%6.2lf%spps",
                 "GPRINT:in:MAX:%6.2lf%spps\\n",
                 "AREA:dout#FFDD88:",
                 "LINE1.25:dout#FF6600:Out ",
		 "GPRINT:out:LAST:%6.2lf%spps",
                 "GPRINT:out:AVERAGE:%6.2lf%spps",
                 "GPRINT:out:MAX:%6.2lf%spps\\n");
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);
  
  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) ) {
    $err = rrd_error();
#    echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}

function errorgraph ($rrd, $graph, $from, $to, $width, $height)
{
    global $rrdtool, $installdir, $mono_font;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";

    $optsa = array( 
                   "--alt-autoscale-max",
                   "-E", 
                   "-l 0",
                   "--start", $from, "--end", $to,
                   "--width", $width, "--height", $height,
                   "DEF:in=$database:INERRORS:AVERAGE",
                   "DEF:out=$database:OUTERRORS:AVERAGE",
                   "CDEF:dout=out,-1,*",
		   "AREA:in#ff3300:",
                   "COMMENT:Errors    Current     Average      Maximum\\n",
                   "LINE1.25:in#ff0000:In ",
                   "GPRINT:in:LAST:%6.2lf%spps",
                   "GPRINT:in:AVERAGE:%6.2lf%spps",
                   "GPRINT:in:MAX:%6.2lf%spps\\n",
                   "AREA:dout#ff6633:",
                   "LINE1.25:out#cc3300:Out",
                   "GPRINT:out:LAST:%6.2lf%spps",
                   "GPRINT:out:AVERAGE:%6.2lf%spps",
                   "GPRINT:out:MAX:%6.2lf%spps\\n",
                   );
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);

    $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error();
 #   echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }

}

function nucastgraph ($rrd, $graph, $from, $to, $width, $height)
{
    global $rrdtool, $installdir, $mono_font;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to,
                 "--width", $width, "--height", $height,
                 "--alt-autoscale-max",
                 "-E", 
                 "-l 0",
                 "DEF:in=$database:INNUCASTPKTS:AVERAGE",
                 "DEF:out=$database:OUTNUCASTPKTS:AVERAGE",
                 "CDEF:dout=out,-1,*",
                 "AREA:in#aa66aa:",
                 "COMMENT:Packets     Current     Average      Maximum\\n",
                 "LINE1.25:in#330033:In   ",
                 "GPRINT:in:LAST:%6.2lf%spps",
                 "GPRINT:in:AVERAGE:%6.2lf%spps",
                 "GPRINT:in:MAX:%6.2lf%spps\\n",
                 "AREA:dout#FFDD88:",
                 "LINE1.25:dout#FF6600:Out  ",
                 "GPRINT:out:LAST:%6.2lf%spps",
                 "GPRINT:out:AVERAGE:%6.2lf%spps",
                 "GPRINT:out:MAX:%6.2lf%spps\\n");
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);

  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) ) {
    $err = rrd_error();
    echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}

function cpugraph ($rrd, $graph , $from, $to, $width, $height)
{
 global $rrdtool, $installdir, $mono_font;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
    $optsa = array( "--start", $from, "--width", $width, "--height", $height, "--vertical-label", $vertical, "--alt-autoscale-max",
                 "-l 0",
                 "-E", 
                 "--title", $title,
                 "DEF:5s=$database:LOAD5S:AVERAGE",
                 "DEF:5m=$database:LOAD5M:AVERAGE",
                 "AREA:5s#FAFDCE:5sec",
                 "LINE1.25:5s#dd8800:",
                 "GPRINT:5s:LAST:Cur\:%6.2lf",
                 "GPRINT:5s:AVERAGE:Avg\: %6.2lf",
		 "GPRINT:5s:MIN:Min\:%6.2lf",
                 "GPRINT:5s:MAX:Max\:%6.2lf\\n",
                 "LINE1.25:5m#aa2200:5min",
                 "GPRINT:5m:LAST:Cur\:%6.2lf",
                 "GPRINT:5m:AVERAGE:Avg\: %6.2lf",
                 "GPRINT:5m:MIN:Min\:%6.2lf",
                 "GPRINT:5m:MAX:Max\:%6.2lf\\n");

  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);

  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) ) {
    $err = rrd_error();
    #echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}

function tempgraph ($rrd, $graph, $from, $to, $width, $height, $title, $vertical)
{
 global $rrdtool, $installdir, $mono_font;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";

  $optsa = array( "--start", $from, "--width", $width, "--height", $height, "--vertical-label", $vertical, "--alt-autoscale-max",
                 "-E",  "-l 0", "--title", $title,
	    "DEF:in=$database:TEMPIN1:AVERAGE",
	    "DEF:out=$database:TEMPOUT1:AVERAGE",
            "LINE1.5:in#cc0000:Inlet ",
            "GPRINT:in:LAST: Cur\:%6.2lf",
            "GPRINT:in:AVERAGE:Avg\: %6.2lf",
            "GPRINT:in:MIN:Min\:%6.2lf",
            "GPRINT:in:MAX:Max\:%6.2lf\\n",
            "LINE1.25:out#009900:Outlet ",
            "GPRINT:out:LAST:Cur\:%6.2lf",
            "GPRINT:out:AVERAGE:Avg\: %6.2lf",
            "GPRINT:out:MIN:Min\:%6.2lf",
            "GPRINT:out:MAX:Max\:%6.2lf\\n");

  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);


  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) ) {
    $err = rrd_error();
#    echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}

function uptimegraph ($rrd, $graph , $from, $to, $width, $height, $title, $vertical)
{
 global $rrdtool, $installdir, $mono_font;
    $rrd = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
    $optsa = array( "--start", $from, "--width", $width, "--height", $height, "--alt-autoscale-max",
                   "-E",  "-l 0",
            "DEF:uptime=$rrd:uptime:AVERAGE",
            "CDEF:cuptime=uptime,86400,/",
            "AREA:cuptime#EEEEEE:Uptime",
            "LINE1.25:cuptime#36393D:",
            "GPRINT:cuptime:LAST:Cur\:%6.2lf",
            "GPRINT:cuptime:AVERAGE:Avg\: %6.2lf",
            "GPRINT:cuptime:MAX:Max\:%6.2lf\\n");
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font",
                                      "--font", "AXIS:6:$mono_font",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);

  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) ) {
    $err = rrd_error();
 #   echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}


function memgraph ($rrd, $graph , $from, $to, $width, $height, $title, $vertical)
{
 global $rrdtool, $installdir, $mono_font;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
    $memrrd = $database;
    $opts = "--start $from \
            --alt-autoscale-max \
            --width $width --height $height \
            -l 0 -E \
            -b 1024 \
             DEF:MEMTOTAL=$memrrd:MEMTOTAL:AVERAGE \
             DEF:IOFREE=$memrrd:IOFREE:AVERAGE \
             DEF:IOUSED=$memrrd:IOUSED:AVERAGE \
             DEF:PROCFREE=$memrrd:PROCFREE:AVERAGE \
             DEF:PROCUSED=$memrrd:PROCUSED:AVERAGE \
	     CDEF:FREE=IOFREE,PROCFREE,+ \
             CDEF:USED=IOUSED,PROCUSED,+ \
             AREA:USED#ee9900:Used \
             AREA:FREE#FAFDCE:Free:STACK \
             LINE1.5:MEMTOTAL#cc0000:";

  if($width <= "300") {$opts .= "\
                                 --font LEGEND:7:$mono_font \
                                 --font AXIS:6:$mono_font \
                                 --font-render-mode normal";}


    `$rrdtool graph $imgfile $opts`;
    return $imgfile;
}

function ip_graph ($rrd, $graph, $from, $to, $width, $height) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--alt-autoscale-max", "-E", "-l 0",
                 "DEF:ipForwDatagrams=$database:ipForwDatagrams:AVERAGE",
                 "DEF:ipInDelivers=$database:ipInDelivers:AVERAGE",
                 "DEF:ipInReceives=$database:ipInReceives:AVERAGE",
                 "DEF:ipOutRequests=$database:ipOutRequests:AVERAGE",
                 "DEF:ipInDiscards=$database:ipInDiscards:AVERAGE",
                 "DEF:ipOutDiscards=$database:ipOutDiscards:AVERAGE",
                 "DEF:ipOutNoRoutes=$database:ipInDiscards:AVERAGE",
                 "COMMENT:Packets/sec    Current    Average   Maximum\\n",
                 "LINE1.25:ipForwDatagrams#cc0000:ForwDgrams ",
                 "GPRINT:ipForwDatagrams:LAST:%6.2lf%s",
                 "GPRINT:ipForwDatagrams:AVERAGE: %6.2lf%s",
                 "GPRINT:ipForwDatagrams:MAX: %6.2lf%s\\n",
                 "LINE1.25:ipInDelivers#00cc00:InDelivers ",
                 "GPRINT:ipInDelivers:LAST:%6.2lf%s",
                 "GPRINT:ipInDelivers:AVERAGE: %6.2lf%s",
                 "GPRINT:ipInDelivers:MAX: %6.2lf%s\\n",
                 "LINE1.25:ipInReceives#006600:InReceives ",
                 "GPRINT:ipInReceives:LAST:%6.2lf%s",
                 "GPRINT:ipInReceives:AVERAGE: %6.2lf%s",
                 "GPRINT:ipInReceives:MAX: %6.2lf%s\\n",
                 "LINE1.25:ipOutRequests#0000cc:OutRequests",
                 "GPRINT:ipOutRequests:LAST:%6.2lf%s",
                 "GPRINT:ipOutRequests:AVERAGE: %6.2lf%s",
                 "GPRINT:ipOutRequests:MAX: %6.2lf%s\\n",
                 "LINE1.25:ipInDiscards#cccc00:InDiscards ",
                 "GPRINT:ipInDiscards:LAST:%6.2lf%s",
                 "GPRINT:ipInDiscards:AVERAGE: %6.2lf%s",
                 "GPRINT:ipInDiscards:MAX: %6.2lf%s\\n",
                 "LINE1.25:ipOutDiscards#330033:OutDiscards",
                 "GPRINT:ipOutDiscards:LAST:%6.2lf%s",
                 "GPRINT:ipOutDiscards:AVERAGE: %6.2lf%s",
                 "GPRINT:ipOutDiscards:MAX: %6.2lf%s\\n",
                 "LINE1.25:ipOutNoRoutes#660000:OutNoRoutes",
                 "GPRINT:ipOutNoRoutes:LAST:%6.2lf%s",
                 "GPRINT:ipOutNoRoutes:AVERAGE: %6.2lf%s",
                 "GPRINT:ipOutNoRoutes:MAX: %6.2lf%s\\n"
		 );
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font", "--font", "AXIS:6:$mono_font", "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) { 
    $err = rrd_error(); echo "rrd_graph() ERROR: $err\n"; return FALSE;
  } else {
    return $imgfile;
  }
}

function icmp_graph ($rrd, $graph, $from, $to, $width, $height) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--alt-autoscale-max", "-E", "-l 0",
                "DEF:icmpInMsgs=$database:icmpInMsgs:AVERAGE",
                 "DEF:icmpOutMsgs=$database:icmpOutMsgs:AVERAGE",
                 "DEF:icmpInErrors=$database:icmpInErrors:AVERAGE",
                 "DEF:icmpOutErrors=$database:icmpOutErrors:AVERAGE",
                 "DEF:icmpInEchos=$database:icmpInEchos:AVERAGE",
                 "DEF:icmpOutEchos=$database:icmpOutEchos:AVERAGE",
                 "DEF:icmpInEchoReps=$database:icmpInEchoReps:AVERAGE",
                 "DEF:icmpOutEchoReps=$database:icmpOutEchoReps:AVERAGE",
                 "COMMENT:Packets/sec    Current    Average   Maximum\\n",
                 "LINE1.25:icmpInMsgs#00cc00:InMsgs     ",
                 "GPRINT:icmpInMsgs:LAST:%6.2lf%s",
                 "GPRINT:icmpInMsgs:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpInMsgs:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpOutMsgs#006600:OutMsgs    ",
                 "GPRINT:icmpOutMsgs:LAST:%6.2lf%s",
                 "GPRINT:icmpOutMsgs:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpOutMsgs:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpInErrors#cc0000:InErrors   ",
                 "GPRINT:icmpInErrors:LAST:%6.2lf%s",
                 "GPRINT:icmpInErrors:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpInErrors:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpOutErrors#660000:OutErrors  ",
                 "GPRINT:icmpOutErrors:LAST:%6.2lf%s",
                 "GPRINT:icmpOutErrors:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpOutErrors:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpInEchos#0066cc:InEchos    ",
                 "GPRINT:icmpInEchos:LAST:%6.2lf%s",
                 "GPRINT:icmpInEchos:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpInEchos:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpOutEchos#003399:OutEchos   ",
                 "GPRINT:icmpOutEchos:LAST:%6.2lf%s",
                 "GPRINT:icmpOutEchos:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpOutEchos:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpInEchoReps#cc00cc:InEchoReps ",
                 "GPRINT:icmpInEchoReps:LAST:%6.2lf%s",
                 "GPRINT:icmpInEchoReps:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpInEchoReps:MAX: %6.2lf%s\\n",
                 "LINE1.25:icmpOutEchoReps#990099:OutEchoReps",
                 "GPRINT:icmpOutEchoReps:LAST:%6.2lf%s",
                 "GPRINT:icmpOutEchoReps:AVERAGE: %6.2lf%s",
                 "GPRINT:icmpOutEchoReps:MAX: %6.2lf%s\\n"
                 );
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font", "--font", "AXIS:6:$mono_font", "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error(); echo "rrd_graph() ERROR: $err\n"; return FALSE;
  } else {
    return $imgfile;
  }
}

function tcp_graph ($rrd, $graph, $from, $to, $width, $height) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--alt-autoscale-max", "-E", "-l 0",
                 "DEF:tcpActiveOpens=$database:tcpActiveOpens:AVERAGE",
                 "DEF:tcpPassiveOpens=$database:tcpPassiveOpens:AVERAGE",
                 "DEF:tcpAttemptFails=$database:tcpAttemptFails:AVERAGE",
                 "DEF:tcpEstabResets=$database:tcpEstabResets:AVERAGE",
                 "DEF:tcpInSegs=$database:tcpInSegs:AVERAGE",
                 "DEF:tcpOutSegs=$database:tcpOutSegs:AVERAGE",
                 "DEF:tcpRetransSegs=$database:tcpRetransSegs:AVERAGE",
                 "COMMENT:Packets/sec    Current    Average   Maximum\\n",
                 "LINE1.25:tcpActiveOpens#00cc00:ActiveOpens ",
                 "GPRINT:tcpActiveOpens:LAST:%6.2lf%s",
                 "GPRINT:tcpActiveOpens:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpActiveOpens:MAX: %6.2lf%s\\n",
                 "LINE1.25:tcpPassiveOpens#006600:PassiveOpens",
                 "GPRINT:tcpPassiveOpens:LAST:%6.2lf%s",
                 "GPRINT:tcpPassiveOpens:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpPassiveOpens:MAX: %6.2lf%s\\n",
                 "LINE1.25:tcpAttemptFails#cc0000:AttemptFails",
                 "GPRINT:tcpAttemptFails:LAST:%6.2lf%s",
                 "GPRINT:tcpAttemptFails:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpAttemptFails:MAX: %6.2lf%s\\n",
                 "LINE1.25:tcpEstabResets#660000:EstabResets ",
                 "GPRINT:tcpEstabResets:LAST:%6.2lf%s",
                 "GPRINT:tcpEstabResets:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpEstabResets:MAX: %6.2lf%s\\n",
                 "LINE1.25:tcpInSegs#0066cc:InSegs      ",
                 "GPRINT:tcpInSegs:LAST:%6.2lf%s",
                 "GPRINT:tcpInSegs:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpInSegs:MAX: %6.2lf%s\\n",
                 "LINE1.25:tcpOutSegs#003399:OutSegs     ",
                 "GPRINT:tcpOutSegs:LAST:%6.2lf%s",
                 "GPRINT:tcpOutSegs:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpOutSegs:MAX: %6.2lf%s\\n",
                 "LINE1.25:tcpRetransSegs#cc00cc:RetransSegs ",
                 "GPRINT:tcpRetransSegs:LAST:%6.2lf%s",
                 "GPRINT:tcpRetransSegs:AVERAGE: %6.2lf%s",
                 "GPRINT:tcpRetransSegs:MAX: %6.2lf%s\\n",
                 );
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font", "--font", "AXIS:6:$mono_font", "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error(); echo "rrd_graph() ERROR: $err\n"; return FALSE;
  } else {
    return $imgfile;
  }
}

function udp_graph ($rrd, $graph, $from, $to, $width, $height) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--alt-autoscale-max", "-E", "-l 0",
                 "DEF:udpInDatagrams=$database:udpInDatagrams:AVERAGE",
                 "DEF:udpOutDatagrams=$database:udpOutDatagrams:AVERAGE",
                 "DEF:udpInErrors=$database:udpInErrors:AVERAGE",
                 "DEF:udpNoPorts=$database:udpNoPorts:AVERAGE",
                 "COMMENT:Packets/sec    Current    Average   Maximum\\n",
                 "LINE1.25:udpInDatagrams#00cc00:InDatagrams ",
                 "GPRINT:udpInDatagrams:LAST:%6.2lf%s",
                 "GPRINT:udpInDatagrams:AVERAGE: %6.2lf%s",
                 "GPRINT:udpInDatagrams:MAX: %6.2lf%s\\n",
                 "LINE1.25:udpOutDatagrams#006600:OutDatagrams",
                 "GPRINT:udpOutDatagrams:LAST:%6.2lf%s",
                 "GPRINT:udpOutDatagrams:AVERAGE: %6.2lf%s",
                 "GPRINT:udpOutDatagrams:MAX: %6.2lf%s\\n",
                 "LINE1.25:udpInErrors#cc0000:InErrors    ",
                 "GPRINT:udpInErrors:LAST:%6.2lf%s",
                 "GPRINT:udpInErrors:AVERAGE: %6.2lf%s",
                 "GPRINT:udpInErrors:MAX: %6.2lf%s\\n",
                 "LINE1.25:udpNoPorts#660000:NoPorts     ",
                 "GPRINT:udpNoPorts:LAST:%6.2lf%s",
                 "GPRINT:udpNoPorts:AVERAGE: %6.2lf%s",
                 "GPRINT:udpNoPorts:MAX: %6.2lf%s\\n",
                 );
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$mono_font", "--font", "AXIS:6:$mono_font", "--font-render-mode", "normal");}
  $opts = array_merge($optsa, $optsb);
  $ret = rrd_graph("$imgfile", $opts, count($opts));
  if( !is_array($ret) ) {
    $err = rrd_error(); echo "rrd_graph() ERROR: $err\n"; return FALSE;
  } else {
    return $imgfile;
  }
}


function fixIOSFeatures($features){
	$features = str_replace("ADVSECURITYK9", "Advanced Security Crypto", $features);
        $features = str_replace("K91P", "Provider Crypto", $features);
	$features = str_replace("K4P", "Provider Crypto", $features);
        $features = str_replace("ADVIPSERVICESK9_WAN", "Adv IP Services Crypto + WAN", $features);
        $features = str_replace("ADVIPSERVICESK9", "Adv IP Services Crypto", $features);
        $features = str_replace("ADVIPSERVICES", "Adv IP Services", $features);
        $features = str_replace("IK9P", "IP Plus Crypto", $features);
        $features = str_replace("SPSERVICESK9", "SP Services Crypto", $features);
        $features = str_replace("PK9SV", "Provider Crypto", $features);
        $features = str_replace("IS", "IP Plus", $features);
        $features = str_replace("IPSERVICESK9", "IP Services Crypto", $features);
        $features = str_replace("BROADBAND", "Broadband", $features);
        $features = str_replace("IPBASE", "IP Base", $features);
        $features = str_replace("IPSERVICE", "IP Services", $features);
        $features = preg_replace("/^P$/", "Service Provider", $features);
        $features = str_replace("IK9S", "IP Plus Crypto", $features);
	$features = str_replace("I6Q4L2", "Layer 2", $features);
        $features = str_replace("I6K2L2Q4", "Layer 2 Crypto", $features);
	$features = str_replace("C3H2S", "Layer 2 SI/EI", $features);
	return $features;
}

function fixIOSHardware($hardware){

        $hardware = preg_replace("/C([0-9]+)/", "Cisco \\1", $hardware);
        $hardware = str_replace("cat4000","Catalyst 4000", $hardware);
        $hardware = str_replace("s3223_rp","Cisco Catalyst 6500 SUP32", $hardware);
        $hardware = str_replace("s222_rp","Cisco Catalyst 6500 SUP2", $hardware);
        $hardware = str_replace("c6sup2_rp","Cisco Catalyst 6500 SUP2", $hardware);
        $hardware = str_replace("s72033_rp","Cisco Catalyst 6500 SUP720 ", $hardware);
        $hardware = str_replace("RSP","Cisco 7500", $hardware);
	$hardware = str_replace("C3200XL", "Cisco Catalyst 3200XL", $hardware);
	$hardware = str_replace("C3550", "Cisco Catalyst 3550", $hardware);
	$hardware = str_replace("C2950", "Cisco Catalyst 2950", $hardware);

	return $hardware;

}

function updateHost ($host, $community, $snmpver)
{
#        $soft = `snmpget -O vq -$snmpver -c $community $host sysDescr.0 | grep IOS | sed s/Cisco\ IOS\ Software\,// | sed s/\"\ //g | sed s/IOS\  \(tm\)\ // | sed s/\,\ RELEASE\ SOFTWARE.*// | sed s/.*\ Software\ // | sed s/\,\ /\|\|/ | sed s/\Version\ // | sed s/,\ EARLY\ DEPLOYMENT\ RELEASE\ SOFTWARE\ .*//`;
        $sysdescr = `snmpget -O vq -$snmpver -c $community $host sysDescr.0`;
        $sysdecr = str_replace("\"","", $sysdescr);
	$location = str_replace("\"","", `snmpget -O vq -v2c -c $community $host sysLocation.0`);
        list ($features, $version) = explode('||', $soft);
        $features = str_replace("(","", $features);
        $features = str_replace(")","", $features);
        $version = str_replace("\n","", $version);
        $version = trim($version);
        $location = trim($location);
        list ($hardware, $features) = explode("-", $features);
        $hardware = fixIOSHardware($hardware);
        $features = fixIOSFeatures($features);
        $sql = "UPDATE `devices` SET `hardware` = '$hardware', `features` = '$features', `version` = '$version', `sysdesc` = '$sysdescr', `location` = '$location' WHERE `hostname` = '$host'";
#	echo("$sql \n");    
#        mysql_query($sql);
}

function getHostOS($host, $community, $snmpver) {
	$sysDescr = trim(`snmpget -O qv -$snmpver -c $community $host sysDescr.0`);
        if ($sysDescr == "") {$sysDescr = trim(`snmpget -O qv -$snmpver -c $community $host 1.3.6.1.2.1.7526.2.4`);}
        echo("\nsnmpget -O qv -$snmpver -c $community $host sysDescr.0\n$sysDescr\n");
	if (strstr($sysDescr, "IOS") !== false) { $os = "IOS"; }
        if (strstr($sysDescr, "FreeBSD") !== false) { $os = "FreeBSD"; }
	if (strstr($sysDescr, "DragonFly")) { $os = "DragonFly"; }
        if (strstr($sysDescr, "NetBSD") !== false) { $os = "NetBSD"; }
	if (strstr($sysDescr, "OpenBSD") !== false) { $os = "OpenBSD"; }
        if (strstr($sysDescr, "Linux") !== false) { $os = "Linux"; }
	if (strstr($sysDescr, "Windows")) { $os = "Windows"; }
        if (strstr($sysDescr, "ProCurve")) { $os = "ProCurve"; }
	if (strstr($sysDescr, "m0n0wall")) { $os = "m0n0wall"; }
	if (strstr($sysDescr, "Voswall")) { $os = "Voswall"; }
	if (strstr($sysDescr, "snom")) { $os = "Snom"; }
	return $os;
}


function createHost ($host, $community, $snmpver){
        $host = trim(strtolower($host));
        $host_os = getHostOS($host, $community, $snmpver); 
	global $valid_os;
        $nullhost = 1;
        echo("$host -> $host_os<br />");
        foreach($valid_os as $os) {
           if ($os == $host_os) {
              $nullhost = '0';
           }
        }
        if($nullhost == '0') {
           $sql = mysql_query("INSERT INTO `devices` (`hostname`, `community`, `os`, `status`) VALUES ('$host', '$community', '$host_os', '1')");
           echo("Created host : $host\n");
        } else {
	   echo("Not added bad host : $host\n");
	}
}

function createInterface ($host, $if, $ifIndex, $up,$up_admin,$speed,$duplex,$mac,$name){
	$sql = "INSERT INTO `interfaces` (`host`,`if`,`ifIndex`, `up`,`up_admin`,`speed`,`duplex`,`mac`,`name`)";
	$sql = $sql . " VALUES ('$host', '$if','$ifIndex','$up','$up_admin','$speed','$duplex','$mac',\"$name\")";
	mysql_query($sql);
}

function updateInterfaceStatus ($id,$ifOperStatus,$ifAdminStatus,$speed,$duplex,$mac,$ifAlias) {
        $sql = "UPDATE `interfaces` SET `up` = '$ifOperStatus', `up_admin` = '$ifAdminStatus', `speed` = '$speed', ";
        $sql .= "`duplex` = '$duplex', `mac` = '$mac', `name` = \"$ifAlias\"  WHERE `id` = '$id'";
        mysql_query($sql);
	echo("$sql\n");
}

function updateInterface ($host, $if, $ifIndex, $up, $up_admin, $speed, $duplex, $mac, $name){
        $sql = "UPDATE `interfaces` SET `up` = '$up',`up_admin` = '$up_admin',`speed` = '$speed',`duplex` = '$duplex',`mac` = '$mac',`name` =  \"$name\"";
        $sql .= " WHERE `host` = '$host' AND `if` = '$if'";
        mysql_query($sql);
}

function isDomainResolves($domain){
     return gethostbyname($domain) != $domain;
}

function hoststatus($id) {
    $sql = mysql_query("SELECT `status` FROM `devices` WHERE `id` = '$id'");
    $result = @mysql_result($sql, 0);
    return $result;
}

function gethostbyid($id) {
     $sql = mysql_query("SELECT `hostname` FROM `devices` WHERE `id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function getifhost($id) {
     $sql = mysql_query("SELECT `host` from `interfaces` WHERE `id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function getifindexbyid($id) {
     $sql = mysql_query("SELECT `ifIndex` FROM `interfaces` WHERE `id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function getifbyid($id) {
     $sql = mysql_query("SELECT `if` FROM `interfaces` WHERE `id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function getidbyname($domain){
     $sql = mysql_query("SELECT `id` FROM `devices` WHERE `hostname` = '$domain'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function gethostosbyid($id) {
     $sql = mysql_query("SELECT `os` FROM `devices` WHERE `id` = '$id'");
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
