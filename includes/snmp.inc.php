<?php

function snmp_get_multi($device, $oids, $options = "-OQUs", $mib = NULL, $mibdir = NULL)
{
  global $debug,$config,$runtime_stats,$mibs_loaded;

  if (is_numeric($device['timeout']) && $device['timeout'] > 0)
  {
     $timeout = $device['timeout'];
  } elseif (isset($config['snmp']['timeout'])) {
     $timeout =  $config['snmp']['timeout'];
  }

  if (is_numeric($device['retries']) && $device['retries'] > 0)
  {
    $retries = $device['retries'];
  } elseif (isset($config['snmp']['retries'])) {
    $retries =  $config['snmp']['retries'];
  }

  if (!isset($device['transport']))
  {
    $device['transport'] = "udp";
  }

  if ($config['snmp']['internal'] == true)
  {
    if ($mib && $mibdir && !$mibs_loaded[$mib])
    {
      @snmp_read_mib($mibdir."/".$mib);
      $mibs_loaded[$mib] = TRUE;
    }

    snmp_set_quick_print(1);
    $oids = explode(" ",trim($oids));
    // s->ms - php snmp extension requires the timeout in microseconds.
    if (isset($timeout)) { $timeout = $timeout*1000*1000; }

    foreach ($oids as $oid)
    {
      if ($device['snmpver'] == "v2c")
      {
        $data = @snmp2_get($device['hostname'].":".$device['port'], $device['community'], $oid, $timeout, $retries);
      }
      elseif ($device['snmpver'] == "v1")
      {
        $data = @snmpget($device['hostname'].":".$device['port'], $device['community'], $oid, $timeout, $retries);
      }

      list($oid, $index) = explode(".", $oid);
      if ($data) { $array[$index][$oid] = $data; }
      else { $array[$index][$oid] = null; }
    }
  }
  else
  {
    $cmd = $config['snmpget'] . " -" . $device['snmpver'] . " -c " . $device['community'] . " ";
    if ($options) { $cmd .= " " . $options; }
    if ($mib) { $cmd .= " -m " . $mib; }
    if ($mibdir) { $cmd .= " -M " . $mibdir; } else { $cmd .= " -M ".$config['mibdir']; }

    if (isset($timeout)) { $cmd .= " -t " . $timeout; }
    if (isset($retries)) { $cmd .= " -r " . $retries; }

    $cmd .= " ".$device['transport'].":".$device['hostname'].":".$device['port']." ".$oids;
    if (!$debug) { $cmd .= " 2>/dev/null"; }
    $data = trim(external_exec($cmd));
    $runtime_stats['snmpget']++;
    foreach (explode("\n", $data) as $entry)
    {
      list($oid,$value) = explode("=", $entry);
      $oid = trim($oid); $value = trim($value);
      list($oid, $index) = explode(".", $oid);
      if (!strstr($value, "at this OID") && isset($oid) && isset($index))
      {
        $array[$index][$oid] = $value;
      }
    }
  }
  return $array;
}

function snmp_get($device, $oid, $options = NULL, $mib = NULL, $mibdir = NULL)
{
  global $debug,$config,$runtime_stats,$mibs_loaded;

  if (is_numeric($device['timeout']) && $device['timeout'] > 0)
  {
     $timeout = $device['timeout'];
  } elseif (isset($config['snmp']['timeout'])) {
     $timeout =  $config['snmp']['timeout'];
  }

  if (is_numeric($device['retries']) && $device['retries'] > 0)
  {
    $retries = $device['retries'];
  } elseif (isset($config['snmp']['retries'])) {
    $retries =  $config['snmp']['retries'];
  }

  if (!isset($device['transport']))
  {
    $device['transport'] = "udp";
  }

  if (strstr($oid,' '))
  {
    echo("BUG: snmp_get called for multiple OIDs: $oid\n");
    echo("Please report this to the Observium team.");
  }

  if ($config['snmp']['internal'] == true)
  {
    if ($mib && $mibdir && !$mibs_loaded[$mib])
    {
      @snmp_read_mib($mibdir."/".$mib);
      $mibs_loaded[$mib] = TRUE;
    }
    snmp_set_quick_print(1);
    // s->ms - php snmp extension requires the timeout in microseconds.
    if (isset($timeout)) { $timeout = $timeout*1000*1000; }
    if ($device['snmpver'] == "v2c")
    {
       $data = @snmp2_get($device['hostname'].":".$device['port'], $device['community'], $oid, $timeout, $retries);
    } elseif ($device['snmpver'] == "v1") {
       $data = @snmpget($device['hostname'].":".$device['port'], $device['community'], $oid, $timeout, $retries);
    }
    if ($debug)  { print "DEBUG: $oid: $data\nDEBUG: cmd: ".$device['transport'].":".$device['hostname'].":".$device['port']." ".$device['community']." ".$oid." ".$timeout." ".$retries."\n"; }
  }
  else
  {
    $cmd = $config['snmpget'] . " -" . $device['snmpver'] . " -c " . $device['community'] . " ";

    if ($options) { $cmd .= " " . $options; }
    if ($mib) { $cmd .= " -m " . $mib; }
    if ($mibdir) { $cmd .= " -M " . $mibdir; } else { $cmd .= " -M ".$config['mibdir']; }
    if (isset($timeout)) { $cmd .= " -t " . $timeout; }
    if (isset($retries)) { $cmd .= " -r " . $retries; }

    $cmd .= " ".$device['transport'].":".$device['hostname'].":".$device['port']." ".$oid;
    if (!$debug) { $cmd .= " 2>/dev/null"; }
    $data = trim(external_exec($cmd));
  }
  $runtime_stats['snmpget']++;
  if (is_string($data) && (preg_match("/No Such Instance/i", $data) || preg_match("/No Such Object/i", $data) || preg_match("/No more variables left/i", $data)))
  {
    return false;
  }
  elseif ($data) { return $data; }
  else { return false; }
}

function snmp_walk($device, $oid, $options = NULL, $mib = NULL, $mibdir = NULL)
{
  global $debug,$config,$runtime_stats;

  if (is_numeric($device['timeout']) && $device['timeout'] > 0)
  {
     $timeout = $device['timeout'];
  } elseif (isset($config['snmp']['timeout']))
  {
     $timeout =  $config['snmp']['timeout'];
  }

  if (is_numeric($device['retries']) && $device['retries'] > 0)
  {
    $retries = $device['retries'];
  } elseif (isset($config['snmp']['retries'])) {
    $retries =  $config['snmp']['retries'];
  }
  if (!isset($device['transport']))
  {
    $device['transport'] = "udp";
  }

  // php has no bulkwalk functionality, so use binary for this.
  if ($device['snmpver'] == 'v1' || $config['os'][$device['os']]['nobulk'])
  {
    $snmpcommand = $config['snmpwalk'];
  }
  else
  {
    $snmpcommand = $config['snmpbulkwalk'];
  }

  $cmd = $snmpcommand . " -" . $device['snmpver'] . " -c " . $device['community'] . " ";
  if ($options) { $cmd .= " $options "; }
  if ($mib) { $cmd .= " -m $mib"; }
  if ($mibdir) { $cmd .= " -M " . $mibdir; } else { $cmd .= " -M ".$config['mibdir']; }
  if (isset($timeout)) { $cmd .= " -t " . $timeout; }
  if (isset($retries)) { $cmd .= " -r " . $retries; }

  $cmd .= " ".$device['transport'].":".$device['hostname'].":".$device['port']." ".$oid;

  if (!$debug) { $cmd .= " 2>/dev/null"; }
  $data = trim(external_exec($cmd));
  $data = str_replace("\"", "", $data);

  if (is_string($data) && (preg_match("/No Such (Object|Instance)/i", $data)))
  {
    $data = false;
  }
  else
  {
    if (preg_match("/No more variables left in this MIB View \(It is past the end of the MIB tree\)$/",$data))  {
    # Bit ugly :-(
    $d_ex = explode("\n",$data);
    unset($d_ex[count($d_ex)-1]);
    $data = implode("\n",$d_ex);
    }
  }
  $runtime_stats['snmpwalk']++;

  return $data;
}

function snmpwalk_cache_cip($device, $oid, $array, $mib = 0)
{
  global $config;

  if (is_numeric($device['timeout']) && $device['timeout'] > 0)
  {
     $timeout = $device['timeout'];
  } elseif (isset($config['snmp']['timeout'])) {
     $timeout =  $config['snmp']['timeout'];
  }

  if (is_numeric($device['retries']) && $device['retries'] > 0)
  {
    $retries = $device['retries'];
  } elseif (isset($config['snmp']['retries'])) {
    $retries =  $config['snmp']['retries'];
  }

  if (!isset($device['transport'])) { $device['transport'] = "udp"; }

  if ($device['snmpver'] == 'v1' || $config['os'][$device['os']]['nobulk'])
  {
    $snmpcommand = $config['snmpwalk'];
  }
  else
  {
    $snmpcommand = $config['snmpbulkwalk'];
  }

  $cmd = $snmpcommand . " -O snQ -" . $device['snmpver'] . " -c " . $device['community'] . " ";
  if ($mib) { $cmd .= " -m $mib"; }
  $cmd .= " -M ".$config['install_dir']."/mibs/";
  if (isset($timeout)) { $cmd .= " -t " . $timeout; }
  if (isset($retries)) { $cmd .= " -r " . $retries; }

  $cmd .= " ".$device['transport'].":".$device['hostname'].":".$device['port']." ".$oid;

  if (!$debug) { $cmd .= " 2>/dev/null"; }
  $data = trim(external_exec($cmd));
  $device_id = $device['device_id'];

  #echo("Caching: $oid\n");
  foreach (explode("\n", $data) as $entry)
  {
    list ($this_oid, $this_value) = preg_split("/=/", $entry);
    $this_oid = trim($this_oid);
    $this_value = trim($this_value);
    $this_oid = substr($this_oid, 30);
    list($ifIndex,$dir,$a,$b,$c,$d,$e,$f) = explode(".", $this_oid);
    $h_a = zeropad(dechex($a));
    $h_b = zeropad(dechex($b));
    $h_c = zeropad(dechex($c));
    $h_d = zeropad(dechex($d));
    $h_e = zeropad(dechex($e));
    $h_f = zeropad(dechex($f));
    $mac = "$h_a$h_b$h_c$h_d$h_e$h_f";
    if ($dir == "1") { $dir = "input"; } elseif ($dir == "2") { $dir = "output"; }
    if ($mac && $dir)
    {
      $array[$ifIndex][$mac][$oid][$dir] = $this_value;
    }
  }
  return $array;
}

function snmp_cache_ifIndex($device)
{
  // FIXME: this has no internal version, and is not yet using our own snmp_*
  global $config;

  if (is_numeric($device['timeout']) && $device['timeout'] > 0)
  {
     $timeout = $device['timeout'];
  } elseif (isset($config['snmp']['timeout'])) {
     $timeout =  $config['snmp']['timeout'];
  }

  if (is_numeric($device['retries']) && $device['retries'] > 0)
  {
    $retries = $device['retries'];
  } elseif (isset($config['snmp']['retries'])) {
    $retries =  $config['snmp']['retries'];
  }

  if (!isset($device['transport'])) { $device['transport'] = "udp"; }

  if ($device['snmpver'] == 'v1' || $config['os'][$device['os']]['nobulk'])
  {
    $snmpcommand = $config['snmpwalk'];
  }
  else
  {
    $snmpcommand = $config['snmpbulkwalk'];
  }

  $cmd = $snmpcommand . " -O Qs -" . $device['snmpver'] . " -c " . $device['community'] . " ";
  $cmd .= " -M ".$config['install_dir']."/mibs/";
  $cmd .= " -m IF-MIB ifIndex";

  if (isset($timeout)) { $cmd .= " -t " . $timeout; }
  if (isset($retries)) { $cmd .= " -r " . $retries; }
  if (!$debug) { $cmd .= " 2>/dev/null"; }
  $data = trim(external_exec($cmd));
  $device_id = $device['device_id'];

  foreach (explode("\n", $data) as $entry)
  {
    list ($this_oid, $this_value) = preg_split("/=/", $entry);
    list ($this_oid, $this_index) = explode(".", $this_oid);
    $this_index = trim($this_index);
    $this_oid = trim($this_oid);
    $this_value = trim($this_value);
    if (!strstr($this_value, "at this OID") && $this_index)
    {
      $array[] = $this_value;
    }
  }

  return $array;
}

function snmpwalk_cache_oid($device, $oid, $array, $mib = NULL, $mibdir = NULL)
{
  $data = snmp_walk($device, $oid, "-OQUs", $mib, $mibdir);
  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry);
    $oid = trim($oid); $value = trim($value);
    list($oid, $index) = explode(".", $oid, 2);
    if (!strstr($value, "at this OID") && isset($oid) && isset($index))
    {
      $array[$index][$oid] = $value;
    }
  }

  return $array;
}

function snmpwalk_cache_multi_oid($device, $oid, $array, $mib = NULL, $mibdir = NULL)
{
  global $cache;

  if (!(is_array($cache['snmp'][$device['device_id']]) && array_key_exists($oid,$cache['snmp'][$device['device_id']])))
  {
    $data = snmp_walk($device, $oid, "-OQUs", $mib, $mibdir);
    foreach (explode("\n", $data) as $entry)
    {
      list($r_oid,$value) = explode("=", $entry);
      $r_oid = trim($r_oid); $value = trim($value);
      $oid_parts = explode(".", $r_oid);
      $r_oid = $oid_parts['0'];
      $index = $oid_parts['1'];
      if (isset($oid_parts['2'])) { $index .= ".".$oid_parts['2']; }
      if (isset($oid_parts['3'])) { $index .= ".".$oid_parts['3']; }
      if (isset($oid_parts['4'])) { $index .= ".".$oid_parts['4']; }
      if (isset($oid_parts['5'])) { $index .= ".".$oid_parts['5']; }
      if (isset($oid_parts['6'])) { $index .= ".".$oid_parts['6']; }
      if (!strstr($value, "at this OID") && isset($r_oid) && isset($index))
      {
        $array[$index][$r_oid] = $value;
      }
    }
    $cache['snmp'][$device['device_id']][$oid] = $array;
  }

  return $cache['snmp'][$device['device_id']][$oid];
}

function snmpwalk_cache_double_oid($device, $oid, $array, $mib = NULL, $mibdir = NULL)
{
  $data = snmp_walk($device, $oid, "-OQUs", $mib, $mibdir);

  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry);
    $oid = trim($oid); $value = trim($value);
    list($oid, $first, $second) = explode(".", $oid);
    if (!strstr($value, "at this OID") && isset($oid) && isset($first) && isset($second))
    {
      $double = $first.".".$second;
      $array[$double][$oid] = $value;
    }
  }

  return $array;
}

function snmpwalk_cache_triple_oid($device, $oid, $array, $mib = NULL, $mibdir = NULL)
{
  $data = snmp_walk($device, $oid, "-OQUs", $mib, $mibdir);

  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry);
    $oid = trim($oid); $value = trim($value);
    list($oid, $first, $second, $third) = explode(".", $oid);
    if (!strstr($value, "at this OID") && isset($oid) && isset($first) && isset($second))
    {
      $index = $first.".".$second.".".$third;
      $array[$index][$oid] = $value;
    }
  }

  return $array;
}

function snmpwalk_cache_twopart_oid($device, $oid, $array, $mib = 0)
{
  global $config;

  if (is_numeric($device['timeout']) && $device['timeout'] > 0)
  {
     $timeout = $device['timeout'];
  } elseif (isset($config['snmp']['timeout'])) {
     $timeout =  $config['snmp']['timeout'];
  }

  if (is_numeric($device['retries']) && $device['retries'] > 0)
  {
    $retries = $device['retries'];
  } elseif (isset($config['snmp']['retries'])) {
    $retries =  $config['snmp']['retries'];
  }

  if (!isset($device['transport']))
  {
    $device['transport'] = "udp";
  }

  if ($device['snmpver'] == 'v1' || $config['os'][$device['os']]['nobulk'])
  {
    $snmpcommand = $config['snmpwalk'];
  }
  else
  {
    $snmpcommand = $config['snmpbulkwalk'];
  }
  $cmd = $snmpcommand . " -O QUs -" . $device['snmpver'] . " -c " . $device['community'] . " ";
  $cmd .= " -M ".$config['install_dir']."/mibs/";
  if ($mib) { $cmd .= " -m $mib"; }
  if (isset($timeout)) { $cmd .= " -t " . $timeout; }
  if (isset($retries)) { $cmd .= " -r " . $retries; }
  $cmd .= " ".$device['transport'].":".$device['hostname'].":".$device['port']." ".$oid;
  if (!$debug) { $cmd .= " 2>/dev/null"; }

  $data = trim(external_exec($cmd));

  $device_id = $device['device_id'];
  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry);
    $oid = trim($oid); $value = trim($value); $value = str_replace("\"", "", $value);
    list($oid, $first, $second) = explode(".", $oid);
    if (!strstr($value, "at this OID") && isset($oid) && isset($first) && isset($second))
    {
      $array[$first][$second][$oid] = $value;
    }
  }

  return $array;
}

function snmpwalk_cache_threepart_oid($device, $oid, $array, $mib = 0)
{
  global $config, $debug;

  if (is_numeric($device['timeout']) && $device['timeout'] > 0)
  {
     $timeout = $device['timeout'];
  } elseif (isset($config['snmp']['timeout'])) {
     $timeout =  $config['snmp']['timeout'];
  }

  if (is_numeric($device['retries']) && $device['retries'] > 0)
  {
    $retries = $device['retries'];
  } elseif (isset($config['snmp']['retries'])) {
    $retries =  $config['snmp']['retries'];
  }

  if (!isset($device['transport']))
  {
    $device['transport'] = "udp";
  }

  if ($device['snmpver'] == 'v1' || $config['os'][$device['os']]['nobulk'])
  {
    $snmpcommand = $config['snmpwalk'];
  }
  else
  {
    $snmpcommand = $config['snmpbulkwalk'];
  }

  $cmd = $snmpcommand . " -O QUs -" . $device['snmpver'] . " -c " . $device['community'] . " ";
  $cmd .= " -M ".$config['install_dir']."/mibs/";
  if ($mib) { $cmd .= " -m $mib"; }
  if (isset($timeout)) { $cmd .= " -t " . $timeout; }
  if (isset($retries)) { $cmd .= " -r " . $retries; }
  $cmd .= " ".$device['transport'].":".$device['hostname'].":".$device['port']." ".$oid;
  if (!$debug) { $cmd .= " 2>/dev/null"; }
  $data = trim(external_exec($cmd));

  $device_id = $device['device_id'];
  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry);
    $oid = trim($oid); $value = trim($value); $value = str_replace("\"", "", $value);
    list($oid, $first, $second, $third) = explode(".", $oid);
    if ($debug) {echo("$entry || $oid || $first || $second || $third\n"); }
    if (!strstr($value, "at this OID") && isset($oid) && isset($first) && isset($second) && isset($third))
    {
      $array[$first][$second][$third][$oid] = $value;
    }
  }

  return $array;
}

function snmp_cache_slotport_oid($oid, $device, $array, $mib = 0)
{
  global $config;

  if (is_numeric($device['timeout']) && $device['timeout'] > 0)
  {
     $timeout = $device['timeout'];
  } elseif (isset($config['snmp']['timeout'])) {
     $timeout =  $config['snmp']['timeout'];
  }

  if (is_numeric($device['retries']) && $device['retries'] > 0)
  {
    $retries = $device['retries'];
  } elseif (isset($config['snmp']['retries'])) {
    $retries =  $config['snmp']['retries'];
  }

  if (!isset($device['transport']))
  {
    $device['transport'] = "udp";
  }

  if ($device['snmpver'] == 'v1' || $config['os'][$device['os']]['nobulk'])
  {
    $snmpcommand = $config['snmpwalk'];
  }
  else
  {
    $snmpcommand = $config['snmpbulkwalk'];
  }

  $cmd = $snmpcommand . " -O QUs -" . $device['snmpver'] . " -c " . $device['community'] . " ";
  if ($mib) { $cmd .= " -m $mib"; }
  $cmd .= " -M ".$config['install_dir']."/mibs/";
  if (isset($timeout)) { $cmd .= " -t " . $timeout; }
  if (isset($retries)) { $cmd .= " -r " . $retries; }
  $cmd .= " ".$device['transport'].":".$device['hostname'].":".$device['port']." ".$oid;
  if (!$debug) { $cmd .= " 2>/dev/null"; }
  $data = trim(external_exec($cmd));
  $device_id = $device['device_id'];

  foreach (explode("\n", $data) as $entry)
  {
    $entry = str_replace($oid.".", "", $entry);
    list($slotport, $value) = explode("=", $entry);
    $slotport = trim($slotport); $value = trim($value);
    if ($array[$slotport]['ifIndex'])
    {
      $ifIndex = $array[$slotport]['ifIndex'];
      $array[$ifIndex][$oid] = $value;
    }
  }

  return $array;
}

function snmp_cache_oid($oid, $device, $array, $mib = 0)
{
  $array = snmpwalk_cache_oid($device, $oid, $array, $mib);
  return $array;
}

function snmp_cache_port_oids($oids, $port, $device, $array, $mib=0)
{
  global $config;

  if (is_numeric($device['timeout']) && $device['timeout'] > 0)
  {
     $timeout = $device['timeout'];
  } elseif (isset($config['snmp']['timeout'])) {
     $timeout =  $config['snmp']['timeout'];
  }

  if (is_numeric($device['retries']) && $device['retries'] > 0)
  {
    $retries = $device['retries'];
  } elseif (isset($config['snmp']['retries'])) {
    $retries =  $config['snmp']['retries'];
  }

  if (!isset($device['transport']))
  {
    $device['transport'] = "udp";
  }

  foreach ($oids as $oid)
  {
    $string .= " $oid.$port";
  }

  $cmd = $config['snmpget'] . " -O vq -" . $device['snmpver'] . " -c " . $device['community'] . " ";
  if (isset($timeout)) { $cmd .= " -t " . $timeout; }
  if (isset($retries)) { $cmd .= " -r " . $retries; }
  $cmd .= " -M ".$config['install_dir']."/mibs/";
  if ($mib) { $cmd .= " -m $mib"; }
  $cmd .= " -t " . $timeout . " -r " . $retries;
  $cmd .= " ".$device['transport'].":".$device['hostname'].":".$device['port']." ".$string;
  if (!$debug) { $cmd .= " 2>/dev/null"; }
  $data = trim(external_exec($cmd));
  $x=0;
  $values = explode("\n", $data);
  #echo("Caching: ifIndex $port\n");
  foreach ($oids as $oid) {
    if (!strstr($values[$x], "at this OID"))
    {
      $array[$port][$oid] = $values[$x];
    }
    $x++;
  }

  return $array;
}

function snmp_cache_portIfIndex($device, $array)
{
  global $config;

  if (is_numeric($device['timeout']) && $device['timeout'] > 0)
  {
     $timeout = $device['timeout'];
  } elseif (isset($config['snmp']['timeout'])) {
     $timeout =  $config['snmp']['timeout'];
  }

  if (is_numeric($device['retries']) && $device['retries'] > 0)
  {
    $retries = $device['retries'];
  } elseif (isset($config['snmp']['retries'])) {
    $retries =  $config['snmp']['retries'];
  }

  if (!isset($device['transport']))
  {
    $device['transport'] = "udp";
  }

  $cmd = $config['snmpwalk'] . " -CI -m CISCO-STACK-MIB -O q -" . $device['snmpver'] . " -c " . $device['community'] . " ";
  $cmd .= " -M ".$config['install_dir']."/mibs/";
  if (isset($timeout)) { $cmd .= " -t " . $timeout; }
  if (isset($retries)) { $cmd .= " -r " . $retries; }
  $cmd .= " ".$device['transport'].":".$device['hostname'].":".$device['port']." portIfIndex";
  $output = trim(external_exec($cmd));
  $device_id = $device['device_id'];

  foreach (explode("\n", $output) as $entry)
  {
    $entry = str_replace("CISCO-STACK-MIB::portIfIndex.", "", $entry);
    list($slotport, $ifIndex) = explode(" ", $entry);
    if ($slotport && $ifIndex) {
      $array[$ifIndex]['portIfIndex'] = $slotport;
      $array[$slotport]['ifIndex'] = $ifIndex;
    }
  }

  return $array;
}

function snmp_cache_portName($device, $array)
{
  global $config;

  if (is_numeric($device['timeout']) && $device['timeout'] > 0)
  {
     $timeout = $device['timeout'];
  } elseif (isset($config['snmp']['timeout'])) {
     $timeout =  $config['snmp']['timeout'];
  }

  if (is_numeric($device['retries']) && $device['retries'] > 0)
  {
    $retries = $device['retries'];
  } elseif (isset($config['snmp']['retries'])) {
    $retries =  $config['snmp']['retries'];
  }

  if (!isset($device['transport']))
  {
    $device['transport'] = "udp";
  }

  $cmd = $config['snmpwalk'] . " -CI -m CISCO-STACK-MIB -O Qs -" . $device['snmpver'] . " -c " . $device['community'] . " ";
  $cmd .= " -M ".$config['install_dir']."/mibs/";
  if (isset($timeout)) { $cmd .= " -t " . $timeout; }
  if (isset($retries)) { $cmd .= " -r " . $retries; }
  $cmd .= " ".$device['transport'].":".$device['hostname'].":".$device['port']." portName";
  $output = trim(external_exec($cmd));
  $device_id = $device['device_id'];
  #echo("Caching: portName\n");

  foreach (explode("\n", $output) as $entry)
  {
    $entry = str_replace("portName.", "", $entry);
    list($slotport, $portName) = explode("=", $entry);
    $slotport = trim($slotport); $portName = trim($portName);
    if ($array[$slotport]['ifIndex'])
    {
      $ifIndex = $array[$slotport]['ifIndex'];
      $array[$slotport]['portName'] = $portName;
      $array[$ifIndex]['portName'] = $portName;
    }
  }

  return $array;
}

?>
