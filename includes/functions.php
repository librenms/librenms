<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage functions
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 */

// Include from PEAR

include_once("Net/IPv4.php");
include_once("Net/IPv6.php");

// Observium Includes
include_once($config['install_dir'] . "/includes/dbFacile.php");

include_once($config['install_dir'] . "/includes/common.php");
include_once($config['install_dir'] . "/includes/rrdtool.inc.php");
include_once($config['install_dir'] . "/includes/billing.php");
include_once($config['install_dir'] . "/includes/cisco-entities.php");
include_once($config['install_dir'] . "/includes/syslog.php");
include_once($config['install_dir'] . "/includes/rewrites.php");
include_once($config['install_dir'] . "/includes/snmp.inc.php");
include_once($config['install_dir'] . "/includes/services.inc.php");
include_once($config['install_dir'] . "/includes/console_colour.php");

$console_color = new Console_Color2();

if ($config['alerts']['email']['enable'])
{
  include_once($config['install_dir'] . "/includes/phpmailer/class.phpmailer.php");
  include_once($config['install_dir'] . "/includes/phpmailer/class.smtp.php");
}

function array_sort($array, $on, $order=SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }
    return $new_array;
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

  $path = $config['install_dir'] . "/includes/discovery/os";
  $dir_handle = @opendir($path) or die("Unable to open $path");
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
  $errors = array();

  $cmd = $config['rrdtool']." fetch -s $period -e -300s $rrd_file AVERAGE | grep : | cut -d\" \" -f 4,5";
  $data = trim(shell_exec($cmd));
  $in_errors = 0;
  $out_errors = 0;
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
  elseif (isset($config['os'][$device['os']]['icon']) && $config['os'][$device['os']]['icon'] && file_exists($config['html_dir'] . "/images/os/" . $config['os'][$device['os']]['icon'] . ".png"))
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
    if (empty($image)) {
        $image = '<img src="' . $config['base_url'] . '/images/os/generic.png" />';
    }
  }

  return $image;
}

function getImageSrc($device)
{
  global $config;

  $device['os'] = strtolower($device['os']);

  if ($device['icon'] && file_exists($config['html_dir'] . "/images/os/" . $device['icon'] . ".png"))
  {
    $image = $config['base_url'] . '/images/os/' . $device['icon'] . '.png';
  }
  elseif ($config['os'][$device['os']]['icon'] && file_exists($config['html_dir'] . "/images/os/" . $config['os'][$device['os']]['icon'] . ".png"))
  {
    $image = $config['base_url'] . '/images/os/' . $config['os'][$device['os']]['icon'] . '.png';
  } else {
    if (file_exists($config['html_dir'] . '/images/os/' . $device['os'] . '.png'))
    {
      $image = $config['base_url'] . '/images/os/' . $device['os'] . '.png';
    }
    if ($device['os'] == "linux")
    {
      $features = strtolower(trim($device['features']));
      list($distro) = explode(" ", $features);
      if (file_exists($config['html_dir'] . "/images/os/$distro" . ".png"))
      {
        $image = $config['base_url'] . '/images/os/' . $distro . '.png';
      }
    }
    if (empty($image)) {
        $image = $config['base_url'] . '/images/os/generic.png';
    }
  }

  return $image;
}

function renamehost($id, $new, $source = 'console')
{
  global $config;

  // FIXME does not check if destination exists!
  $host = dbFetchCell("SELECT `hostname` FROM `devices` WHERE `device_id` = ?", array($id));
  if (rename($config['rrd_dir']."/$host",$config['rrd_dir']."/$new") === TRUE) {
      dbUpdate(array('hostname' => $new), 'devices', 'device_id=?', array($id));
      log_event("Hostname changed -> $new ($source)", $id, 'system');
  } else {
      echo "Renaming of $host failed\n";
      log_event("Renaming of $host failed", $id, 'system'); 
  }
}

function delete_device($id)
{
  global $config, $debug;
  $ret = '';

  $host = dbFetchCell("SELECT hostname FROM devices WHERE device_id = ?", array($id));
  if( empty($host) ) {
    return "No such host.";
  }

  foreach (dbFetch("SELECT * FROM `ports` WHERE `device_id` = ?", array($id)) as $int_data)
  {
    $int_if = $int_data['ifDescr'];
    $int_id = $int_data['port_id'];
    delete_port($int_id);
    $ret .= "Removed interface $int_id ($int_if)\n";
  }

  $fields = array('device_id','host');
  foreach( $fields as $field ) {
    foreach( dbFetch("SELECT table_name FROM information_schema.columns WHERE table_schema = ? AND column_name = ?",array($config['db_name'],$field)) as $table ) {
      $table = $table['table_name'];
      $entries = (int) dbDelete($table, "`$field` =  ?", array($id));
      if( $entries > 0 && $debug === true ) {
        $ret .= "$field@$table = #$entries\n";
      }
    }
  }

  $ex = shell_exec("bash -c '( [ ! -d ".trim($config['rrd_dir'])."/".$host." ] || rm -vrf ".trim($config['rrd_dir'])."/".$host." 2>&1 ) && echo -n OK'");
  $tmp = explode("\n",$ex);
  if( $tmp[sizeof($tmp)-1] != "OK" ) {
    $ret .= "Could not remove files:\n$ex\n";
  }

  $ret .= "Removed device $host\n";
  return $ret;
}

function addHost($host, $snmpver, $port = '161', $transport = 'udp', $quiet = '0', $poller_group = '0', $force_add = '0')
{
  global $config;

  list($hostshort) = explode(".", $host);
  // Test Database Exists
  if (dbFetchCell("SELECT COUNT(*) FROM `devices` WHERE `hostname` = ?", array($host)) == '0')
  {
      // Test reachability
      if ($force_add == 1 || isPingable($host))
      {
        if (empty($snmpver))
        {
          // Try SNMPv2c
          $snmpver = 'v2c';
          $ret = addHost($host, $snmpver, $port, $transport, $quiet, $poller_group, $force_add);
          if (!$ret)
          {
            //Try SNMPv3
            $snmpver = 'v3';
            $ret = addHost($host, $snmpver, $port, $transport, $quiet, $poller_group, $force_add);
            if (!$ret)
            {
              // Try SNMPv1
              $snmpver = 'v1';
              return addHost($host, $snmpver, $port, $transport, $quiet, $poller_group, $force_add);
            } else {
              return $ret;
            }
          } else {
            return $ret;
          }
        }

        if ($snmpver === "v3")
        {
          // Try each set of parameters from config
          foreach ($config['snmp']['v3'] as $v3)
          {
            $device = deviceArray($host, NULL, $snmpver, $port, $transport, $v3);
            if($quiet == '0') { print_message("Trying v3 parameters " . $v3['authname'] . "/" .  $v3['authlevel'] . " ... "); }
            if ($force_add == 1 || isSNMPable($device))
            {
              $snmphost = snmp_get($device, "sysName.0", "-Oqv", "SNMPv2-MIB");
              if (empty($snmphost) or ($snmphost == $host || $hostshort = $host))
              {
                $device_id = createHost ($host, NULL, $snmpver, $port, $transport, $v3, $poller_group);
                return $device_id;
              } else {
                if($quiet == '0') {print_error("Given hostname does not match SNMP-read hostname ($snmphost)!"); }
              }
            } else {
              if($quiet == '0') {print_error("No reply on credentials " . $v3['authname'] . "/" .  $v3['authlevel'] . " using $snmpver"); }
            }
          }
        }
        elseif ($snmpver === "v2c" or $snmpver === "v1")
        {
          // try each community from config
          foreach ($config['snmp']['community'] as $community)
          {
            $device = deviceArray($host, $community, $snmpver, $port, $transport, NULL);
            if($quiet == '0') { print_message("Trying community $community ..."); }
            if ($force_add == 1 || isSNMPable($device))
            {
              $snmphost = snmp_get($device, "sysName.0", "-Oqv", "SNMPv2-MIB");
              if (empty($snmphost) || ($snmphost && ($snmphost == $host || $hostshort = $host)))
              {
                $device_id = createHost ($host, $community, $snmpver, $port, $transport,array(),$poller_group);
                return $device_id;
              } else {
                if($quiet == '0') { print_error("Given hostname does not match SNMP-read hostname ($snmphost)!"); }
              }
            } else {
              if($quiet == '0') { print_error("No reply on community $community using $snmpver"); }
            }
          }
        }
        else
        {
          if($quiet == '0') { print_error("Unsupported SNMP Version \"$snmpver\"."); }
        }

        if (!$device_id)
        {
          // Failed SNMP
          if($quiet == '0') { print_error("Could not reach $host with given SNMP community using $snmpver"); }
        }
      } else {
        // failed Reachability
        if($quiet == '0') { print_error("Could not ping $host"); }
      }
  } else {
    // found in database
    if($quiet == '0') { print_error("Already got host $host"); }
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

function cidr2netmask($netmask)
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
  if(empty($pos)) {
     // Support for Hikvision
     $pos = snmp_get($device, "SNMPv2-SMI::enterprises.39165.1.1.0", "-Oqv", "SNMPv2-MIB");
  }
  if ($pos === '' || $pos === false)
  {
    return false;
  } else {
    return true;
  }
}

function isPingable($hostname,$device_id = FALSE)
{
   global $config;

   $fping_params = '';
   if(is_numeric($config['fping_options']['retries']) || $config['fping_options']['retries'] > 1) {
       $fping_params .= ' -r ' . $config['fping_options']['retries'];
   }
   if(is_numeric($config['fping_options']['timeout']) || $config['fping_options']['timeout'] > 1) {
       $fping_params .= ' -t ' . $config['fping_options']['timeout'];
   }
   $status = shell_exec($config['fping'] . "$fping_params -e $hostname 2>/dev/null");
   $response = array();
   if (strstr($status, "alive"))
   {
     $response['result'] = TRUE;
   } else {
     $status = shell_exec($config['fping6'] . "$fping_params -e $hostname 2>/dev/null");
     if (strstr($status, "alive"))
     {
       $response['result'] = TRUE;
     } else {
       $response['result'] = FALSE;
     }
   }
   if(is_numeric($device_id) && !empty($device_id))
   {
     preg_match('/(\d+\.*\d*) (ms)/', $status, $time);
     $response['last_ping_timetaken'] = $time[1];
   }
   return($response);
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

function createHost($host, $community = NULL, $snmpver, $port = 161, $transport = 'udp', $v3 = array(), $poller_group='0')
{
  global $config;
  $host = trim(strtolower($host));

  if (is_numeric($poller_group) === FALSE) {
      $poller_group = $config['distributed_poller_group'];
  }
  $device = array('hostname' => $host,
                  'sysName' => $host,
                  'community' => $community,
                  'port' => $port,
                  'transport' => $transport,
                  'status' => '1',
                  'snmpver' => $snmpver,
                  'poller_group' => $poller_group
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
  $ipv6_2 = array();

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
  $ipv6_split = array();
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

function send_mail($emails,$subject,$message,$html=false) {
	global $config;
	if( is_array($emails) || ($emails = parse_email($emails)) ) {
		if( !class_exists("PHPMailer",false) )
			include_once($config['install_dir'] . "/includes/phpmailer/class.phpmailer.php");
		$mail = new PHPMailer();
		$mail->Hostname = php_uname('n');
		if (empty($config['email_from']))
			$config['email_from'] = '"' . $config['project_name'] . '" <' . $config['email_user'] . '@'.$mail->Hostname.'>';
		foreach (parse_email($config['email_from']) as $from => $from_name)
			$mail->setFrom($from, $from_name);
		foreach ($emails as $email => $email_name)
			$mail->addAddress($email, $email_name);
		$mail->Subject = $subject;
		$mail->XMailer = $config['project_name_version'];
		$mail->CharSet = 'utf-8';
		$mail->WordWrap = 76;
		$mail->Body = $message;
		if( $html )
			$mail->isHTML(true);
		switch (strtolower(trim($config['email_backend']))) {
			case 'sendmail':
				$mail->Mailer = 'sendmail';
				$mail->Sendmail = $config['email_sendmail_path'];
			break;
			case 'smtp':
				$mail->isSMTP();
				$mail->Host       = $config['email_smtp_host'];
				$mail->Timeout    = $config['email_smtp_timeout'];
				$mail->SMTPAuth   = $config['email_smtp_auth'];
				$mail->SMTPSecure = $config['email_smtp_secure'];
				$mail->Port       = $config['email_smtp_port'];
				$mail->Username   = $config['email_smtp_username'];
				$mail->Password   = $config['email_smtp_password'];
				$mail->SMTPDebug  = false;
			break;
			default:
				$mail->Mailer = 'mail';
			break;
		}
		return $mail->send() ? true : $mail->ErrorInfo;
	}
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
        $message_header = $config['page_title_prefix']."\n\n";		// FIXME: use different config element
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
        if( ($err = send_mail($emails,$title, $message_header.$message.$message_footer)) !== true) { echo "Mailer Error: ".$err."\n"; }
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

  global $config, $debug;

  if (strstr($port['ifDescr'], "irtual"))
  {
    $valid = 0;
  } else {
    $valid = 1;
    $if = strtolower($port['ifDescr']);
    $fringe = $config['bad_if'];
    if( is_array($config['os'][$device['os']]['bad_if']) ) {
      $fringe = array_merge($config['bad_if'],$config['os'][$device['os']]['bad_if']);
    }
    foreach ($fringe as $bi)
    {
      if (strstr($if, $bi))
      {
        $valid = 0;
        if ($debug) { echo("ignored : $bi : $if"); }
      }
    }
    if (is_array($config['bad_if_regexp']))
    {
      $fringe = $config['bad_if_regexp'];
      if( is_array($config['os'][$device['os']]['bad_if_regexp']) ) {
        $fringe = array_merge($config['bad_if_regexp'],$config['os'][$device['os']]['bad_if_regexp']);
      }
      foreach ($fringe as $bi)
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
      $fringe = $config['bad_iftype'];
      if( is_array($config['os'][$device['os']]['bad_iftype']) ) {
        $fringe = array_merge($config['bad_iftype'],$config['os'][$device['os']]['bad_iftype']);
      }
      foreach ($fringe as $bi)
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
    if ($device['os'] == "dlink") { $valid = 1; }
  }

  return $valid;
}

function scan_new_plugins()
{

  global $config, $debug;

  $installed = 0; // Track how many plugins we install.

  if(file_exists($config['plugin_dir']))
  {
    $plugin_files = scandir($config['plugin_dir']);
    foreach($plugin_files as $name)
    {
      if(is_dir($config['plugin_dir'].'/'.$name))
      {
        if($name != '.' && $name != '..')
        {
          if(is_file($config['plugin_dir'].'/'.$name.'/'.$name.'.php') && is_file($config['plugin_dir'].'/'.$name.'/'.$name.'.inc.php'))
          {
            $plugin_id = dbFetchRow("SELECT `plugin_id` FROM `plugins` WHERE `plugin_name` = '$name'");
            if(empty($plugin_id))
            {
              if(dbInsert(array('plugin_name' => $name, 'plugin_active' => '0'), 'plugins'))
              {
                $installed++;
              }
            }
          }
        }
      }
    }
  }

  return( $installed );

}

function validate_device_id($id)
{

  global $config;
  if(empty($id) || !is_numeric($id))
  {
    $return = false;
  }
  else
  {
    $device_id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `device_id` = ?", array($id));
    if($device_id == $id)
    {
      $return = true;
    }
    else
    {
      $return = false;
    }
  }
  return($return);
}

// The original source of this code is from Stackoverflow (www.stackoverflow.com).
// http://stackoverflow.com/questions/6054033/pretty-printing-json-with-php
// Answer provided by stewe (http://stackoverflow.com/users/3202187/ulk200
if (!defined('JSON_UNESCAPED_SLASHES'))
    define('JSON_UNESCAPED_SLASHES', 64);
if (!defined('JSON_PRETTY_PRINT'))
    define('JSON_PRETTY_PRINT', 128);
if (!defined('JSON_UNESCAPED_UNICODE'))
    define('JSON_UNESCAPED_UNICODE', 256);

function _json_encode($data, $options = 448)
{
    if (version_compare(PHP_VERSION, '5.4', '>=')) {
        return json_encode($data, $options);
    }
    else {
	return _json_format(json_encode($data), $options);
    }
}

function _json_format($json, $options = 448)
{
    $prettyPrint = (bool) ($options & JSON_PRETTY_PRINT);
    $unescapeUnicode = (bool) ($options & JSON_UNESCAPED_UNICODE);
    $unescapeSlashes = (bool) ($options & JSON_UNESCAPED_SLASHES);

    if (!$prettyPrint && !$unescapeUnicode && !$unescapeSlashes)
    {
        return $json;
    }

    $result = '';
    $pos = 0;
    $strLen = strlen($json);
    $indentStr = ' ';
    $newLine = "\n";
    $outOfQuotes = true;
    $buffer = '';
    $noescape = true;

    for ($i = 0; $i < $strLen; $i++)
    {
        // Grab the next character in the string
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ('"' === $char && $noescape)
        {
            $outOfQuotes = !$outOfQuotes;
        }

        if (!$outOfQuotes)
        {
            $buffer .= $char;
            $noescape = '\\' === $char ? !$noescape : true;
            continue;
        }
        elseif ('' !== $buffer)
        {
            if ($unescapeSlashes)
            {
                $buffer = str_replace('\\/', '/', $buffer);
            }

            if ($unescapeUnicode && function_exists('mb_convert_encoding'))
            {
                // http://stackoverflow.com/questions/2934563/how-to-decode-unicode-escape-sequences-like-u00ed-to-proper-utf-8-encoded-cha
                $buffer = preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
                    function ($match)
                    {
                        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                    }, $buffer);
            }

            $result .= $buffer . $char;
            $buffer = '';
            continue;
        }
        elseif(false !== strpos(" \t\r\n", $char))
        {
            continue;
        }

        if (':' === $char)
        {
            // Add a space after the : character
            $char .= ' ';
        }
        elseif (('}' === $char || ']' === $char))
        {
            $pos--;
            $prevChar = substr($json, $i - 1, 1);

            if ('{' !== $prevChar && '[' !== $prevChar)
            {
                // If this character is the end of an element,
                // output a new line and indent the next line
                $result .= $newLine;
                for ($j = 0; $j < $pos; $j++)
                {
                    $result .= $indentStr;
                }
            }
            else
            {
                // Collapse empty {} and []
                $result = rtrim($result) . "\n\n" . $indentStr;
            }
        }

        $result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line
        if (',' === $char || '{' === $char || '[' === $char)
        {
            $result .= $newLine;

            if ('{' === $char || '[' === $char)
            {
                $pos++;
            }

            for ($j = 0; $j < $pos; $j++)
            {
                $result .= $indentStr;
            }
        }
    }
    // If buffer not empty after formating we have an unclosed quote
    if (strlen($buffer) > 0)
    {
        //json is incorrectly formatted
        $result = false;
    }

    return $result;
}

function convert_delay($delay) {
    $delay = preg_replace('/\s/','',$delay);
    if(strstr($delay, 'm',TRUE)) {
        $delay_sec = $delay * 60;
    } elseif(strstr($delay, 'h',TRUE)) {
        $delay_sec = $delay * 3600;
    } elseif(strstr($delay, 'd',TRUE)) {
        $delay_sec = $delay * 86400;
    } elseif(is_numeric($delay)) {
        $delay_sec = $delay;
    } else {
        $delay_sec = 300;
    }
    return($delay_sec);
}

function guidv4($data) {
    // http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid#15875555
    // From: Jack http://stackoverflow.com/users/1338292/ja%CD%A2ck
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function set_curl_proxy($post)
{
    global $config;
    if (isset($_ENV['https_proxy'])) {
	$tmp = rtrim($_ENV['https_proxy'], "/");
	$proxystr = str_replace(array("http://", "https://"), "", $tmp);
	$config['callback_proxy'] = $proxystr;
	echo "Setting proxy to ".$proxystr." (from https_proxy=".$_ENV['https_proxy'].")\n";
    }
    if (isset($config['callback_proxy'])) {
	echo "Using ".$config['callback_proxy']." as proxy\n";
	curl_setopt($post, CURLOPT_PROXY, $config['callback_proxy']);
    }
}

function target_to_id($target) {
    if( $target[0].$target[1] == "g:" ) {
        $target = "g".dbFetchCell('SELECT id FROM device_groups WHERE name = ?',array(substr($target,2)));
    } else {
        $target = dbFetchCell('SELECT device_id FROM devices WHERE hostname = ?',array($target));
    }
    return $target;
}

function id_to_target($id) {
    if( $id[0] == "g" ) {
        $id = 'g:'.dbFetchCell("SELECT name FROM device_groups WHERE id = ?",array(substr($id,1)));
    } else {
        $id = dbFetchCell("SELECT hostname FROM devices WHERE device_id = ?",array($id));
    }
    return $id;
}

function first_oid_match($device, $list) {
    foreach ($list as $item) {
	$tmp = trim(snmp_get($device, $item, "-Ovq"), '" ');
	if (!empty($tmp)) {
	    return $tmp;
	}
    }
}

function hex_to_ip($hex) {
    $return = "";
    if (filter_var($hex, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === FALSE && filter_var($hex, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === FALSE) {
        $hex_exp = explode(' ', $hex);
        foreach ($hex_exp as $item) {
            if (!empty($item) && $item != "\"") {
                $return .= hexdec($item).'.';
            }
        }
        $return = substr($return, 0, -1);
    } else {
        $return = $hex;
    }
    return $return;
}
function fix_integer_value($value) {
    if ($value < 0) {
        $return = 4294967296+$value;
    } else {
        $return = $value;
    }
    return $return;
}
