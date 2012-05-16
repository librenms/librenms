<?php

## FIXME : use db functions properly

# $device_id_host = @dbFetchCell("SELECT device_id FROM devices WHERE `hostname` = '".mres($entry['host'])."' OR `sysName` = '".mres($entry['host'])."'");

# $device_id_ip = @dbFetchCell("SELECT device_id FROM ipv4_addresses AS A, ports AS I WHERE A.ipv4_address = '" . $entry['host']."' AND I.port_id = A.port_id");

function get_cache($host, $value)
{
  global $dev_cache;

  if (!isset($dev_cache[$host][$value]))
  {
    switch($value)
    {
      case 'device_id':
        // Try by hostname
        $dev_cache[$host]['device_id'] = dbFetchCell('SELECT `device_id` FROM devices WHERE `hostname` = ? OR `sysName` = ?', array($host, $host));
        // If failed, try by IP
        if (!is_numeric($dev_cache[$host]['device_id']))
        {
          $dev_cache[$host]['device_id'] = dbFetchCell('SELECT `device_id` FROM `ipv4_addresses` AS A, `ports` AS I WHERE A.ipv4_address = ? AND I.port_id = A.port_id', array($host));
        }
        break;
      case 'os':
        $dev_cache[$host]['os'] = dbFetchCell('SELECT `os` FROM devices WHERE `device_id` = ?', array(get_cache($host, 'device_id')));
        break;
      case 'version':
        $dev_cache[$host]['version'] = dbFetchCell('SELECT `version` FROM devices WHERE `device_id`= ?', array(get_cache($host, 'device_id')));
        break;
      default:
        return null;
    }
  }
  return $dev_cache[$host][$value];
}

function process_syslog($entry, $update)
{
  global $config, $dev_cache;

  foreach ($config['syslog_filter'] as $bi)
  {
    if (strpos($entry['msg'], $bi) !== FALSE)
    {
      print_r($entry);
      echo('D-'.$bi);
      return $entry;
    }
  }

  $entry['device_id'] = get_cache($entry['host'], 'device_id');
  if ($entry['device_id'])
  {
    $os = get_cache($entry['host'], 'os');

    if (in_array($os, array('ios', 'iosxe', 'catos')))
    {
      $matches = array();
#      if (preg_match('#%(?P<program>.*):( ?)(?P<msg>.*)#', $entry['msg'], $matches)) {
#        $entry['msg'] = $matches['msg'];
#        $entry['program'] = $matches['program'];
#      }
#      unset($matches);

      if (strstr($entry[msg], "%"))
      {
        $entry['msg'] = preg_replace("/^%(.+?):\ /", "\\1||", $entry['msg']);
        list(,$entry[msg]) = split(": %", $entry['msg']);
        $entry['msg'] = "%" . $entry['msg'];
        $entry['msg'] = preg_replace("/^%(.+?):\ /", "\\1||", $entry['msg']);
      }
      else
      {
        $entry['msg'] = preg_replace("/^.*[0-9]:/", "", $entry['msg']);
        $entry['msg'] = preg_replace("/^[0-9][0-9]\ [A-Z]{3}:/", "", $entry['msg']);
        $entry['msg'] = preg_replace("/^(.+?):\ /", "\\1||", $entry['msg']);
      }

      $entry['msg'] = preg_replace("/^.+\.[0-9]{3}:/", "", $entry['msg']);
      $entry['msg'] = preg_replace("/^.+-Traceback=/", "Traceback||", $entry['msg']);

      list($entry['program'], $entry['msg']) = explode("||", $entry['msg']);
      $entry['msg'] = preg_replace("/^[0-9]+:/", "", $entry['msg']);

      if (!$entry['program'])
      {
         $entry['msg'] = preg_replace("/^([0-9A-Z\-]+?):\ /", "\\1||", $entry['msg']);
         list($entry['program'], $entry['msg']) = explode("||", $entry['msg']);
      }

      if (!$entry['msg']) { $entry['msg'] = $entry['program']; unset ($entry['program']); }

    } elseif($os == 'linux' and get_cache($entry['host'], 'version') == 'Point') {
      // Cisco WAP200 and similar
      $matches = array();
      if (preg_match('#Log: \[(?P<program>.*)\] - (?P<msg>.*)#', $entry['msg'], $matches)) {
        $entry['msg'] = $matches['msg'];
        $entry['program'] = $matches['program'];
      }
      unset($matches);

    } elseif($os == 'linux') {
      $matches = array();
      // User_CommonName/123.213.132.231:39872 VERIFY OK: depth=1, /C=PL/ST=Malopolska/O=VLO/CN=v-lo.krakow.pl/emailAddress=root@v-lo.krakow.pl
      if ($entry['facility'] == 'daemon' and preg_match('#/([0-9]{1,3}\.) {3}[0-9]{1,3}:[0-9]{4,} ([A-Z]([A-Za-z])+( ?)) {2,}:#', $entry['msg']))
      {
        $entry['program'] = 'OpenVPN';
      }
      // pop3-login: Login: user=<username>, method=PLAIN, rip=123.213.132.231, lip=123.213.132.231, TLS
      // POP3(username): Disconnected: Logged out top=0/0, retr=0/0, del=0/1, size=2802
      elseif($entry['facility'] == 'mail' and preg_match('#^(((pop3|imap)\-login)|((POP3|IMAP)\(.*\))):', $entry['msg']))
      {
        $entry['program'] = 'Dovecot';
      }
      // pam_krb5(sshd:auth): authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
      // pam_krb5[sshd:auth]: authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
      elseif(preg_match('#^(?P<program>(.*((\(|\[).*(\)|\])))):(?P<msg>.*)$#', $entry['msg'], $matches))
      {
        $entry['msg'] = $matches['msg'];
        $entry['program'] = $matches['program'];
      }

      // SYSLOG CONNECTION BROKEN; FD='6', SERVER='AF_INET(123.213.132.231:514)', time_reopen='60'
      // pam_krb5: authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
      ## Disabled because broke this:
      // diskio.c: don't know how to handle 10 request
      #elseif($pos = strpos($entry['msg'], ';') or $pos = strpos($entry['msg'], ':')) {
      #  $entry['program'] = substr($entry['msg'], 0, $pos);
      #  $entry['msg'] = substr($entry['msg'], $pos+1);
      #}
      // fallback, better than nothing...
      elseif(empty($entry['program']) and !empty($entry['facility']))
      {
        $entry['program'] = $entry['facility'];
      }
      unset($matches);
    }

    if (!isset($entry['program']))
    {
      $entry['program'] = $entry['msg'];
      unset($entry['msg']);
    }

    $entry['program'] = strtoupper($entry['program']);
    array_walk($entry, 'trim');

    if ($update)
      dbInsert(
        array(
          'device_id' => $entry['device_id'],
          'program' => $entry['program'],
          'facility' => $entry['facility'],
          'priority' => $entry['priority'],
          'level' => $entry['level'],
          'tag' => $entry['tag'],
          'msg' => $entry['msg'],
          'timestamp' => $entry['timestamp']
        ),
        'syslog'
      );
    unset($os);
  }
  return $entry;
}

?>
