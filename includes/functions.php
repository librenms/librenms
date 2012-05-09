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
include_once($config['install_dir'] . "/includes/dbFacile.php");
include_once($config['install_dir'] . "/includes/console_colour.php");

if ($config['alerts']['email']['enable'])
{
  include_once($config['install_dir'] . "/includes/phpmailer/class.phpmailer.php");
  include_once($config['install_dir'] . "/includes/phpmailer/class.smtp.php");
}

function nicecase($item)
{
  switch ($item)
  {
    case "dbm":
      return "dBm";
    case "mysql":
      return" MySQL";
    case "powerdns":
      return "PowerDNS";
    case "bind":
      return "BIND";
    default:
      return ucfirst($item);
  }
}

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

function getHostOS($device)
{
  global $config, $debug;

  $sysDescr    = snmp_get ($device, "SNMPv2-MIB::sysDescr.0", "-Ovq");
  $sysObjectId = snmp_get ($device, "SNMPv2-MIB::sysObjectID.0", "-Ovqn");

  if ($debug)
  {
    echo("| $sysDescr | $sysObjectId | ");
  }

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

function getImage($device)
{
  global $config;

  $device['os'] = strtolower($device['os']);

  if ($device['icon'] && file_exists($config['html_dir'] . "/images/os/" . $device['icon'] . ".png"))
  {
    $image = '<img src="' . $config['base_url'] . '/images/os/' . $device['icon'] . '.png" />';
  }
  elseif ($config['os'][$device['os']]['icon'] && file_exists($config['html_dir'] . "/images/os/" . $config['os'][$device['os']]['icon'] . ".png"))
  {
    $image = '<img src="' . $config['base_url'] . '/images/os/' . $config['os'][$device['os']]['icon'] . '.png" />';
  } else {
    if (file_exists($config['html_dir'] . '/images/os/' . $device['os'] . '.png'))
    {
      $image = '<img src="' . $config['base_url'] . '/images/os/' . $device['os'] . '.png" />';
    }
    if ($device['os'] == "linux")
    {
      $features = strtolower(trim($device['features']));
      list($distro) = explode(" ", $features);
      if (file_exists($config['html_dir'] . "/images/os/$distro" . ".png"))
      {
        $image = '<img src="' . $config['base_url'] . '/images/os/' . $distro . '.png" />';
      }
    }
  }

  return $image;
}

function renamehost($id, $new, $source = 'console')
{
  global $config;

  ## FIXME does not check if destination exists!
  $host = dbFetchCell("SELECT `hostname` FROM `devices` WHERE `device_id` = ?", array($id));
  rename($config['rrd_dir']."/$host",$config['rrd_dir']."/$new");
  $return = dbUpdate(array('hostname' => $new), 'devices', 'device_id=?', array($id));
  log_event("Hostname changed -> $new ($source)", $id, 'system');
}

function delete_device($id)
{
  global $config;

  $host = dbFetchCell("SELECT hostname FROM devices WHERE device_id = ?", array($id));

  foreach (dbFetch("SELECT * FROM `ports` WHERE `device_id` = ?", array($id)) as $int_data)
  {
    $int_if = $int_data['ifDescr'];
    $int_id = $int_data['interface_id'];
    delete_port($int_id);
    $ret .= "Removed interface $int_id ($int_if)\n";
  }

  dbDelete('devices', "`device_id` =  ?", array($id));

  $device_tables = array('entPhysical', 'devices_attribs', 'devices_perms', 'bgpPeers', 'vlans', 'vrfs', 'storage', 'alerts', 'eventlog',
                         'syslog', 'ports', 'services', 'alerts', 'toner', 'frequency', 'current', 'sensors');

  foreach ($device_tables as $table)
  {
    dbDelete($table, "`device_id` =  ?", array($id));
  }

  shell_exec("rm -rf ".trim($config['rrd_dir'])."/$host");

  $ret = "Removed device $host\n";
  return $ret;
}

function addHost($host, $snmpver, $port = '161', $transport = 'udp')
{
  global $config;

  list($hostshort) = explode(".", $host);
  /// Test Database Exists
  if (dbFetchCell("SELECT COUNT(*) FROM `devices` WHERE `hostname` = ?", array($host)) == '0')
  {
    /// Test DNS lookup
    if (gethostbyname($host) != $host)
    {
      /// Test reachability
      if (isPingable($host))
      {
        $added = 0;

        if (empty($snmpver))
        {
          // Try SNMPv2c
          $snmpver = 'v2c';
          if (!addHost($host, $snmpver))
          {
            //Try SNMPv3
            $snmpver = 'v3';
            if (!addHost($host, $snmpver))
            {
              // Try SNMPv1
              $snmpver = 'v1';
              if (!addHost($host, $snmpver))
              {
                return 0;
              }
            }
          }
        }

        if ($snmpver === "v3")
        {
          // Try each set of parameters from config
          foreach ($config['snmp']['v3'] as $v3)
          {
            $device = deviceArray($host, NULL, $snmpver, $port, $transport, $v3);
            print_message("Trying v3 parameters " . $v3['authname'] . "/" .  $v3['authlevel'] . " ... ");
            if (isSNMPable($device))
            {
              $snmphost = snmp_get($device, "sysName.0", "-Oqv", "SNMPv2-MIB");
              if (empty($snmphost) or ($snmphost == $host || $hostshort = $host))
              {
                $device_id = createHost ($host, NULL, $snmpver, $port, $transport, $v3);
                return $device_id;
              } else {
                print_error("Given hostname does not match SNMP-read hostname ($snmphost)!");
              }
            } else {
              print_error("No reply on credentials " . $v3['authname'] . "/" .  $v3['authlevel'] . " using $snmpver");
            }
          }
        }
        elseif ($snmpver === "v2c" or $snmpver === "v1")
        {
          /// try each community from config
          foreach ($config['snmp']['community'] as $community)
          {
            $device = deviceArray($host, $community, $snmpver, $port, $transport, NULL);
            print_message("Trying community $community ...");
            if (isSNMPable($device))
            {
              $snmphost = snmp_get($device, "sysName.0", "-Oqv", "SNMPv2-MIB");
              if ($snmphost == "" || ($snmphost && ($snmphost == $host || $hostshort = $host)))
              {
                $device_id = createHost ($host, $community, $snmpver, $port, $transport);
                return $device_id;
              } else {
                print_error("Given hostname does not match SNMP-read hostname ($snmphost)!");
              }
            } else {
              print_error("No reply on community $community using $snmpver");
            }
          }
        }
        else
        {
          print_error("Unsupported SNMP Version \"$snmpver\".");
        }

        if (!$device_id)
        {
          /// Failed SNMP
          print_error("Could not reach $host with given SNMP community using $snmpver");
        }
      } else {
        /// failed Reachability
        print_error("Could not ping $host"); }
    } else {
      /// Failed DNS lookup
      print_error("Could not resolve $host"); }
  } else {
    /// found in database
    print_error("Already got host $host"); 
  }
  return 0;
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

function deviceArray($host, $community, $snmpver, $port = 161, $transport = 'udp', $v3)
{
  $device = array();
  $device['hostname'] = $host;
  $device['port'] = $port;
  $device['transport'] = $transport;

  $device['snmpver'] = $snmpver;
  if ($snmpver === "v2c" or $snmpver === "v1")
  {
    $device['community'] = $community;
  }
  elseif ($snmpver === "v3")
  {
    $device['authlevel']  = $v3['authlevel'];
    $device['authname']   = $v3['authname'];
    $device['authpass']   = $v3['authpass'];
    $device['authalgo']   = $v3['authalgo'];
    $device['cryptopass'] = $v3['cryptopass'];
    $device['cryptoalgo'] = $v3['cryptoalgo'];
  }

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

function utime()
{
  $time = explode(" ", microtime());
  $usec = (double)$time[0];
  $sec = (double)$time[1];
  return $sec + $usec;
}

function createHost($host, $community = NULL, $snmpver, $port = 161, $transport = 'udp', $v3 = array())
{
  $host = trim(strtolower($host));

  $device = array('hostname' => $host,
                  'sysName' => $host,
                  'community' => $community,
                  'port' => $port,
                  'transport' => $transport,
                  'status' => '1',
                  'snmpver' => $snmpver
            );

  $device = array_merge($device, $v3);

  $device['os'] = getHostOS($device);

  if ($device['os'])
  {

    $device_id = dbInsert($device, 'devices');

    if ($device_id)
    {
      return($device_id);
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
  return dbFetchCell("SELECT `status` FROM `devices` WHERE `device_id` = ?", array($id));
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

  # Workaround stupid Microsoft bug in Windows 2008 -- this is fixed length!
  # < fenestro> "because whoever implemented this mib for Microsoft was ignorant of RFC 2578 section 7.7 (2)"
  if (count($ipv6) == 17 && $ipv6[0] == 16)
  {
    array_shift($ipv6);
  }

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

# Use this function to write to the eventlog table
function log_event($text, $device = NULL, $type = NULL, $reference = NULL)
{
  global $debug;

  if (!is_array($device)) { $device = device_by_id_cache($device); }

  $insert = array('host' => ($device['device_id'] ? $device['device_id'] : "NULL"),
                  'reference' => ($reference ? $reference : "NULL"),
                  'type' => ($type ? $type : "NULL"),
                  'datetime' => array("NOW()"),
                  'message' => $text);

  dbInsert($insert, 'eventlog');
}

// Parse string with emails. Return array with email (as key) and name (as value)
function parse_email($emails)
{
  $result = array();
  $regex = '/^[\"\']?([^\"\']+)[\"\']?\s{0,}<([^@]+@[^>]+)>$/';
  if (is_string($emails))
  {
    $emails = preg_split('/[,;]\s{0,}/', $emails);
    foreach ($emails as $email)
    {
      if (preg_match($regex, $email, $out, PREG_OFFSET_CAPTURE))
      {
        $result[$out[2][0]] = $out[1][0];
      } else {
        if (strpos($email, "@")) { $result[$email] = NULL; }
      }
    }
  } else {
    // Return FALSE if input not string
    return FALSE;
  }
  return $result;
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
      $emails = parse_email($email);
      if ($emails)
      {
        $message_header = $config['page_title_prefix']."\n\n";
        $message_footer = "\n\nE-mail sent to: ";
        $i = 0;
        $count = count($emails);
        foreach ($emails as $email => $email_name)
        {
          $i++;
          $message_footer .= $email;
          if ($i < $count)
          {
            $message_footer .= ", ";
          } else {
            $message_footer .= "\n";
          }
        }
        $message_footer .= "E-mail sent at: " . date($config['timestamp_format']) . "\n";
        $mail = new PHPMailer();
        $mail->Hostname = php_uname('n');
        if (empty($config['email_from']))
        {
          $config['email_from'] = '"Observium" <observium@'.php_uname('n').'>'; // Default "From:"
        }
        foreach (parse_email($config['email_from']) as $from => $from_name)
        {
          $mail->SetFrom($from, $from_name); // From:
        }
        foreach ($emails as $email => $email_name) { $mail->AddAddress($email, $email_name); } // To:
        $mail->Subject = $title; // Subject:
        $mail->XMailer = 'Observium ' . $config['version']; // X-Mailer:
        $mail->CharSet = 'utf-8';
        $mail->WordWrap = 76;
        $mail->Body = $message_header . $message . $message_footer;
        switch (strtolower(trim($config['email_backend']))) {
          case 'sendmail':
            $mail->Mailer = 'sendmail';
            $mail->Sendmail = $config['email_sendmail_path'];
            break;
          case 'smtp':
            $mail->IsSMTP();
            $mail->Host       = $config['email_smtp_host'];
            $mail->Timeout    = $config['email_smtp_timeout'];
            $mail->SMTPAuth   = $config['email_smtp_auth'];
            $mail->SMTPSecure = $config['email_smtp_secure'];
            if ($config['email_smtp_secure'] == "ssl" && $config['email_smtp_port'] == 25) {
              $mail->Port     = 465; // Default port for SSL
            }
            $mail->Port       = $config['email_smtp_port'];
            $mail->Username   = $config['email_smtp_username'];
            $mail->Password   = $config['email_smtp_password'];
            $mail->SMTPDebug  = false;
            break;
          default:
            $mail->Mailer = 'mail';
            break;
        }
        // Sending email
        if (!$mail->Send()) { echo "Mailer Error: " . $mail->ErrorInfo . "\n"; }
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
  global $device, $config, $debug, $valid;

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

function is_port_valid($port, $device)
{

  global $config;

  if (!strstr($port['ifDescr'], "irtual"))
  {
    $valid = 1;
    $if = strtolower($port['ifDescr']);
    foreach ($config['bad_if'] as $bi)
    {
      if (strstr($if, $bi))
      {
        $valid = 0;
        if ($debug) { echo("ignored : $bi : $if"); }
      }
    }
    if (is_array($config['bad_if_regexp']))
    {
      foreach ($config['bad_if_regexp'] as $bi)
      {
        if (preg_match($bi ."i", $if))
        {
          $valid = 0;
          if ($debug) { echo("ignored : $bi : ".$if); }
        }
      }
    }
    if (is_array($config['bad_iftype']))
    {
      foreach ($config['bad_iftype'] as $bi)
      {
      if (strstr($port['ifType'], $bi))
        {
          $valid = 0;
          if ($debug) { echo("ignored ifType : ".$port['ifType']." (matched: ".$bi." )"); }
        }
      }
    }
    if (empty($port['ifDescr'])) { $valid = 0; }
    if ($device['os'] == "catos" && strstr($if, "vlan")) { $valid = 0; }
  } else {
    $valid = 0;
  }

  return $valid;
}

?>
