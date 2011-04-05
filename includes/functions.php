<?php

## Include from PEAR

include_once("Net/IPv4.php");
include_once("Net/IPv6.php");

## Observium Includes

include_once($config['install_dir'] . "/includes/common.php");
include_once($config['install_dir'] . "/includes/rrdtool.inc.php");
include_once($config['install_dir'] . "/includes/billing.php");
include_once($config['install_dir'] . "/includes/cisco-entities.php");
include_once($config['install_dir'] . "/includes/syslog.php");
include_once($config['install_dir'] . "/includes/rewrites.php");
include_once($config['install_dir'] . "/includes/snmp.inc.php");
include_once($config['install_dir'] . "/includes/services.inc.php");

function mac_clean_to_readable($mac)
{
  $r = substr($mac, 0, 2);
  $r .= ":".substr($mac, 2, 2);
  $r .= ":".substr($mac, 4, 2);
  $r .= ":".substr($mac, 6, 2);
  $r .= ":".substr($mac, 8, 2);
  $r .= ":".substr($mac, 10, 2);

  return($r);
}

function only_alphanumeric($string)
{
  return preg_replace('/[^a-zA-Z0-9]/', '', $string);
}

function logfile($string)
{
  global $config;

  $fd = fopen($config['log_file'],'a');
  fputs($fd,$string . "\n");
  fclose($fd);
}

function shorthost($hostname, $len=16)
{
  $parts = explode(".", $hostname);
  $shorthost = $parts[0];
  $i = 1;
  while ($i < count($parts) && strlen($shorthost.'.'.$parts[$i]) < $len)
  {
    $shorthost = $shorthost.'.'.$parts[$i];
    $i++;
  }
  return ($shorthost);
}

function device_array($device_id)
{
  $sql = "SELECT * FROM `devices` WHERE `device_id` = '".$device_id."'";
  $query = mysql_query($sql);
  $device = mysql_fetch_array($query);
  return $device;
}

function getHostOS($device)
{
  global $config;

  $sysDescr    = snmp_get ($device, "SNMPv2-MIB::sysDescr.0", "-Ovq");
  $sysObjectId = snmp_get ($device, "SNMPv2-MIB::sysObjectID.0", "-Ovqn");

  echo("| $sysDescr | $sysObjectId | ");

  $dir_handle = @opendir($config['install_dir'] . "/includes/discovery/os") or die("Unable to open $path");
  while ($file = readdir($dir_handle))
  {
    if (preg_match("/.php$/", $file))
    {
      include($config['install_dir'] . "/includes/discovery/os/" . $file);
    }
  }
  closedir($dir_handle);

  if ($os) { return $os; } else { return "generic"; }
}

function percent_colour($perc)
{
  $r = min(255, 5 * ($perc - 25));
  $b = max(0, 255 - (5 * ($perc + 25)));

  return sprintf('#%02x%02x%02x', $r, $b, $b);
}

function interface_errors($rrd_file, $period = '-1d') // Returns the last in/out errors value in RRD
{
  global $config;

  $cmd = $config['rrdtool']." fetch -s $period -e -300s $rrd_file AVERAGE | grep : | cut -d\" \" -f 4,5";
  $data = trim(shell_exec($cmd));
  foreach (explode("\n", $data) as $entry)
  {
    list($in, $out) = explode(" ", $entry);
    $in_errors += ($in * 300);
    $out_errors += ($out * 300);
  }
  $errors['in'] = round($in_errors);
  $errors['out'] = round($out_errors);

  return $errors;
}

function getImage($host)
{
  global $config;
  $sql = "SELECT * FROM `devices` WHERE `device_id` = '$host'";
  $data = mysql_fetch_array(mysql_query($sql));
  $type = strtolower($data['os']);
  if ($config['os'][$type]['icon'] && file_exists($config['html_dir'] . "/images/os/" . $config['os'][$type]['icon']  . ".png"))
  {
    $image = '<img src="'.$config['base_url'].'/images/os/'.$config['os'][$type]['icon'].'.png" />';
  } elseif ($config['os'][$type]['icon'] && file_exists($config['html_dir'] . "/images/os/". $config['os'][$type]['icon'] . ".gif"))
  {
    $image = '<img src="'.$config['base_url'].'/images/os/'.$config['os'][$type]['icon'].'.gif" />';
  } else {
    if (file_exists($config['html_dir'] . "/images/os/$type" . ".png")){ $image = '<img src="'.$config['base_url'].'/images/os/'.$type.'.png" />';
    } elseif (file_exists($config['html_dir'] . "/images/os/$type" . ".gif")){ $image = '<img src="'.$config['base_url'].'/images/os/'.$type.'.gif" />'; }
    if ($type == "linux")
    {
      $features = strtolower(trim($data['features']));
      list($distro) = split(" ", $features);
      if (file_exists($config['html_dir'] . "/images/os/$distro" . ".png")){ $image = '<img src="'.$config['base_url'].'/images/os/'.$distro.'.png" />';
      } elseif (file_exists($config['html_dir'] . "/images/os/$distro" . ".gif")){ $image = '<img src="'.$config['base_url'].'/images/os/'.$distro.'.gif" />'; }
    }
  }

  return $image;
}

function renamehost($id, $new, $source = 'console')
{
  global $config;

  $host = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '$id'"), 0);
  rename($config['rrd_dir']."/$host",$config['rrd_dir']."/$new");
  mysql_query("UPDATE devices SET hostname = '$new' WHERE device_id = '$id'");
  log_event("Hostname changed -> $new ($source)", $id, 'system');
}

function delete_port($int_id)
{
  global $config;

  $interface = mysql_fetch_assoc(mysql_query("SELECT * FROM `ports` AS P, `devices` AS D WHERE P.interface_id = '".$int_id."' AND D.device_id = P.device_id"));
  mysql_query("DELETE from `adjacencies` WHERE `interface_id` = '$int_id'");
  mysql_query("DELETE from `links` WHERE `local_interface_id` = '$int_id'");
  mysql_query("DELETE from `links` WHERE `remote_interface_id` = '$int_id'");
  mysql_query("DELETE from `ipaddr` WHERE `interface_id` = '$int_id'");
  mysql_query("DELETE from `ip6adjacencies` WHERE `interface_id` = '$int_id'");
  mysql_query("DELETE from `ip6addr` WHERE `interface_id` = '$int_id'");
  mysql_query("DELETE from `mac_accounting` WHERE `interface_id` = '$int_id'");
  mysql_query("DELETE FROM `bill_ports` WHERE `port_id` = '$int_id'");
  mysql_query("DELETE from `pseudowires` WHERE `interface_id` = '$int_id'");
  mysql_query("DELETE FROM `ports` WHERE `interface_id` = '$int_id'");
  unlink(trim($config['rrd_dir'])."/".trim($interface['hostname'])."/".$interface['ifIndex'].".rrd");
}

function delete_device($id)
{
  global $config;

  $host = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '$id'"), 0);
  mysql_query("DELETE FROM `devices` WHERE `device_id` = '$id'");
  $int_query = mysql_query("SELECT * FROM `ports` WHERE `device_id` = '$id'");
  while ($int_data = mysql_fetch_array($int_query))
  {
    $int_if = $int_data['ifDescr'];
    $int_id = $int_data['interface_id'];
    delete_port($int_id);
    $ret .= "Removed interface $int_id ($int_if)\n";
  }

  mysql_query("DELETE FROM `entPhysical` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `devices_attribs` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `devices_perms` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `bgpPeers` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `vlans` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `vrfs` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `storage` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `alerts` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `eventlog` WHERE `host` = '$id'");
  mysql_query("DELETE FROM `syslog` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `ports` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `services` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `alerts` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `toner` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `frequency` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `current` WHERE `device_id` = '$id'");
  mysql_query("DELETE FROM `sensors` WHERE `device_id` = '$id'");
  shell_exec("rm -rf ".trim($config['rrd_dir'])."/$host");

  $ret = "Removed Device $host\n";
  return $ret;
}

function addHost($host, $community, $snmpver, $port = 161, $transport = 'udp')
{
  global $config;

  list($hostshort) = explode(".", $host);
  if (isDomainResolves($host))
  {
    if (isPingable($host))
    {
      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `hostname` = '$host'"), 0) == '0')
      {
        # FIXME internalize -- but we don't have $device yet!
        # FIXME this needs to be addhost.php's content instead, kindof, also use this function there then.
        $snmphost = shell_exec($config['snmpget'] ." -m SNMPv2-MIB -Oqv -$snmpver -c $community $host:$port sysName.0");
        if ($snmphost == $host || $hostshort = $host)
        {
          createHost($host, $community, $snmpver, $port, $transport);
        } else { echo("Given hostname does not match SNMP-read hostname!\n"); }
      } else { echo("Already got host $host\n"); }
    } else { echo("Could not ping $host\n"); }
  } else { echo("Could not resolve $host\n"); }
}

function scanUDP($host, $port, $timeout)
{
  $handle = fsockopen($host, $port, $errno, $errstr, 2);
  socket_set_timeout ($handle, $timeout);
  $write = fwrite($handle,"\x00");
  if (!$write) { next; }
  $startTime = time();
  $header = fread($handle, 1);
  $endTime = time();
  $timeDiff = $endTime - $startTime;
  if ($timeDiff >= $timeout)
  {
    fclose($handle); return 1;
  } else { fclose($handle); return 0; }
}

function deviceArray($host, $community, $snmpver, $port = 161, $transport = 'udp')
{
  $device = array();
  $device['hostname'] = $host;
  $device['port'] = $port;
  $device['community'] = $community;
  $device['snmpver'] = $snmpver;
  $device['transport'] = $transport;

  return $device;
}

function netmask2cidr($netmask)
{
  $addr = Net_IPv4::parseAddress("1.2.3.4/$netmask");
  return $addr->bitmask;
}

function cidr2netmask()
{
  return (long2ip(ip2long("255.255.255.255") << (32-$netmask)));
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

  if ($format == "short")
  {
    if ($yearsDiff > '0') { $uptime .= $yearsDiff . "y "; }
    if ($daysDiff > '0') { $uptime .= $daysDiff . "d "; }
    if ($hrsDiff > '0') { $uptime .= $hrsDiff . "h "; }
    if ($minsDiff > '0') { $uptime .= $minsDiff . "m "; }
    if ($secsDiff > '0') { $uptime .= $secsDiff . "s "; }
  }
  else
  {
    if ($yearsDiff > '0') { $uptime .= $yearsDiff . " years, "; }
    if ($daysDiff > '0') { $uptime .= $daysDiff . " day" . ($daysDiff != 1 ? 's' : '') . ", "; }
    if ($hrsDiff > '0') { $uptime .= $hrsDiff     . "h "; }
    if ($minsDiff > '0') { $uptime .= $minsDiff   . "m "; }
    if ($secsDiff > '0') { $uptime .= $secsDiff   . "s "; }
  }
  return trim($uptime);
}

function isSNMPable($device)
{
  global $config;

  $pos = snmp_get($device, "sysObjectID.0", "-Oqv", "SNMPv2-MIB");
  if ($pos === '' || $pos === false)
  {
    return false;
  } else {
    return true;
  }
}

function isPingable($hostname)
{
   global $config;
   $status = shell_exec($config['fping'] . " $hostname 2>/dev/null");
   if (strstr($status, "alive"))
   {
     return TRUE;
   } else {
     $status = shell_exec($config['fping6'] . " $hostname 2>/dev/null");
     if (strstr($status, "alive"))
     {
       return TRUE;
     } else {
       return FALSE;
     }
   }
}

function is_odd($number)
{
  return $number & 1; // 0 = even, 1 = odd
}

function isValidInterface($if)
{
      global $config;
      $if = strtolower($if);
      $nullintf = 0;
      foreach ($config['bad_if'] as $bi)
      {
        $pos = strpos($if, $bi);
        if ($pos !== FALSE)
        {
          $nullintf = 1;
          echo("$if matched $bi \n");
        }
      }
      if (preg_match('/serial[0-9]:/', $if)) { $nullintf = '1'; }
      if ($nullintf != '1')
      {
        return 1;
      } else {
        return 0;
      }
}

function utime()
{
  $time = explode(" ", microtime());
  $usec = (double)$time[0];
  $sec = (double)$time[1];
  return $sec + $usec;
}

function createHost($host, $community, $snmpver, $port = 161, $transport = 'udp')
{
  $host = trim(strtolower($host));
  $device = deviceArray($host, $community, $snmpver, $port, $transport);
  $host_os = getHostOS($device);

  if ($host_os)
  {
    $sql = mysql_query("INSERT INTO `devices` (`hostname`, `sysName`, `community`, `port`, `transport`, `os`, `status`,`snmpver`) VALUES ('$host', '$host', '$community', '$port', '$transport', '$host_os', '1','$snmpver')");
    if (mysql_affected_rows())
    {
      return("Created host : $host (id:".mysql_insert_id().") (os:$host_os)");
    }
    else
    {
      return FALSE;
    }
  }
  else
  {
    return FALSE;
  }
}

function isDomainResolves($domain)
{
  return (gethostbyname($domain) != $domain || count(dns_get_record($domain)) != 0);
}

function hoststatus($id)
{
  $sql = mysql_query("SELECT `status` FROM `devices` WHERE `device_id` = '$id'");
  $result = @mysql_result($sql, 0);

  return $result;
}

function match_network($nets, $ip, $first=false)
{
  $return = false;
  if (!is_array ($nets)) $nets = array ($nets);
  foreach ($nets as $net)
  {
    $rev = (preg_match ("/^\!/", $net)) ? true : false;
    $net = preg_replace ("/^\!/", "", $net);
    $ip_arr  = explode('/', $net);
    $net_long = ip2long($ip_arr[0]);
    $x        = ip2long($ip_arr[1]);
    $mask    = long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
    $ip_long  = ip2long($ip);
    if ($rev)
    {
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
  for ($i = 0;$i < 8;$i++) { $ipv6_ex[$i] = zeropad($ipv6_ex[$i],4); }
  $ipv6_ip = implode('',$ipv6_ex);
  for ($i = 0;$i < 32;$i+=2) $ipv6_split[] = hexdec(substr($ipv6_ip,$i,2));

  return implode('.',$ipv6_split);
}

function discover_process_ipv6($ifIndex,$ipv6_address,$ipv6_prefixlen,$ipv6_origin)
{
  global $valid_v6,$device,$config;

  $ipv6_network = Net_IPv6::getNetmask("$ipv6_address/$ipv6_prefixlen") . '/' . $ipv6_prefixlen;
  $ipv6_compressed = Net_IPv6::compress($ipv6_address);

  if (Net_IPv6::getAddressType($ipv6_address) == NET_IPV6_LOCAL_LINK)
  {
    # ignore link-locals (coming from IPV6-MIB)
    return;
  }

  if (mysql_result(mysql_query("SELECT count(*) FROM `ports`
        WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) != '0' && $ipv6_prefixlen > '0' && $ipv6_prefixlen < '129' && $ipv6_compressed != '::1')
  {
    $i_query = "SELECT interface_id FROM `ports` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'";
    $interface_id = mysql_result(mysql_query($i_query), 0);
    if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ipv6_networks` WHERE `ipv6_network` = '$ipv6_network'"), 0) < '1')
    {
      mysql_query("INSERT INTO `ipv6_networks` (`ipv6_network`) VALUES ('$ipv6_network')");
      echo("N");
    }

    if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ipv6_networks` WHERE `ipv6_network` = '$ipv6_network'"), 0) < '1')
    {
      mysql_query("INSERT INTO `ipv6_networks` (`ipv6_network`) VALUES ('$ipv6_network')");
      echo("N");
    }

    $ipv6_network_id = @mysql_result(mysql_query("SELECT `ipv6_network_id` from `ipv6_networks` WHERE `ipv6_network` = '$ipv6_network'"), 0);

    if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ipv6_addresses` WHERE `ipv6_address` = '$ipv6_address' AND `ipv6_prefixlen` = '$ipv6_prefixlen' AND `interface_id` = '$interface_id'"), 0) == '0')
    {
     mysql_query("INSERT INTO `ipv6_addresses` (`ipv6_address`, `ipv6_compressed`, `ipv6_prefixlen`, `ipv6_origin`, `ipv6_network_id`, `interface_id`)
                                   VALUES ('$ipv6_address', '$ipv6_compressed', '$ipv6_prefixlen', '$ipv6_origin', '$ipv6_network_id', '$interface_id')");
     echo("+");
    }
    else
    {
      echo(".");
    }
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
      $result = trim(str_replace('"', '', $txt[4]));
      $cache['astext'][$asn] = $result;
      return $result;
    }
  }
}

# FIXME DEPRECATED -- only used in dead file includes/polling/interfaces.inc.php - if we no longer need that one, this can go too.
function eventlog($eventtext,$device_id = "", $interface_id = "")
{
  $event_query = "INSERT INTO eventlog (host, interface, datetime, message) VALUES (" . ($device_id ? $device_id : "NULL");
  $event_query .= ", " . ($interface_id ? $interface_id : "NULL") . ", NOW(), '" . mysql_escape_string($eventtext) . "')";
  mysql_query($event_query);
}

# Use this function to write to the eventlog table
function log_event($text, $device = NULL, $type = NULL, $reference = NULL)
{
  global $debug;

  if (!is_array($device)) { $device = device_by_id_cache($device); }

  $event_query = "INSERT INTO eventlog (host, reference, type, datetime, message) VALUES (" . ($device['device_id'] ? $device['device_id'] : "NULL");
  $event_query .= ", '" . ($reference ? $reference : "NULL") . "', '" . ($type ? $type : "NULL") . "', NOW(), '" . mres($text) . "')";
  if ($debug) { echo($event_query . "\n"); }
  mysql_query($event_query);
}

function notify($device,$title,$message)
{
  global $config;

  if ($config['alerts']['email']['enable'])
  {
    if (!get_dev_attrib($device,'disable_notify'))
    {
      if ($config['alerts']['email']['default_only'])
      {
        $email = $config['alerts']['email']['default'];
      } else {
        if (get_dev_attrib($device,'override_sysContact_bool'))
        {
          $email = get_dev_attrib($device,'override_sysContact_string');
        }
        elseif ($device['sysContact'])
        {
          $email = $device['sysContact'];
        } else {
          $email = $config['alerts']['email']['default'];
        }
      }
      if ($email)
      {
        mail($email, $title, $message, $config['email_headers']);
      }
    }
  }
}

function formatCiscoHardware(&$device, $short = false)
{
  if ($device['os'] == "ios")
  {
    if ($device['hardware'])
    {
      if (preg_match("/^WS-C([A-Za-z0-9]+).*/", $device['hardware'], $matches))
      {
        if (!$short)
        {
           $device['hardware'] = "Cisco " . $matches[1] . " (" . $device['hardware'] . ")";
        }
        else
        {
           $device['hardware'] = "Cisco " . $matches[1];
        }
      }
      elseif (preg_match("/^CISCO([0-9]+)$/", $device['hardware'], $matches))
      {
        $device['hardware'] = "Cisco " . $matches[1];
      }
    }
    else
    {
      if (preg_match("/Cisco IOS Software, C([A-Za-z0-9]+) Software.*/", $device['sysDescr'], $matches))
      {
        $device['hardware'] = "Cisco " . $matches[1];
      }
      elseif (preg_match("/Cisco IOS Software, ([0-9]+) Software.*/", $device['sysDescr'], $matches))
      {
        $device['hardware'] = "Cisco " . $matches[1];
      }
    }
  }
}

# from http://ditio.net/2008/11/04/php-string-to-hex-and-hex-to-string-functions/
function hex2str($hex)
{
  $string='';

  for ($i = 0; $i < strlen($hex)-1; $i+=2)
  {
    $string .= chr(hexdec($hex[$i].$hex[$i+1]));
  }

  return $string;
}

# Convert an SNMP hex string to regular string
function snmp_hexstring($hex)
{
  return hex2str(str_replace(' ','',str_replace(' 00','',$hex)));
}

# Check if the supplied string is an SNMP hex string
function isHexString($str)
{
  return preg_match("/^[a-f0-9][a-f0-9]( [a-f0-9][a-f0-9])*$/is",trim($str));
}

# Include all .inc.php files in $dir
function include_dir($dir, $regex = "")
{
  global $device, $config, $debug;
  if ($regex == "")
  {
    $regex = "/\.inc\.php$/";
  }

  if ($handle = opendir($config['install_dir'] . '/' . $dir))
  {
    while (false !== ($file = readdir($handle)))
    {
      if (filetype($config['install_dir'] . '/' . $dir . '/' . $file) == 'file' && preg_match($regex, $file))
      {
        if ($debug) { echo("Including: " . $config['install_dir'] . '/' . $dir . '/' . $file . "\n"); }
        include($config['install_dir'] . '/' . $dir . '/' . $file);
      }
    }

    closedir($handle);
  }
}

?>
