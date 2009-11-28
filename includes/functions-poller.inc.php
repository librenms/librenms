<?php

function snmp_cache_cip($oid, $device, $array, $mib = 0) {
  global $config;
  $cmd  = $config['snmpbulkwalk'] . " -O snQ -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  if($mib) { $cmd .= "-m $mib "; }
  $cmd .= $oid;
  $data = trim(shell_exec($cmd));
  $device_id = $device['device_id'];
  #echo("Caching: $oid\n");
  foreach(explode("\n", $data) as $entry) {
    list ($this_oid, $this_value) = split("=", $entry);
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
    if($dir == "1") { $dir = "input"; } elseif($dir == "2") { $dir = "output"; }
    if($mac && $dir) {
      $array[$device_id][$ifIndex][$mac][$oid][$dir] = $this_value;
    }
  }
  return $array;
}


function snmp_cache_ifIndex($device) {

global $config;
  $cmd  = $config['snmpbulkwalk'] . " -O Qs -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  $cmd .= "-m IF-MIB ifIndex";
  $data = trim(shell_exec($cmd));
  $device_id = $device['device_id'];
  foreach(explode("\n", $data) as $entry) {
    list ($this_oid, $this_value) = split("=", $entry);
    list ($this_oid, $this_index) = explode(".", $this_oid);
    $this_index = trim($this_index);
    $this_oid = trim($this_oid);
    $this_value = trim($this_value);
    if(!strstr($this_value, "at this OID") && $this_index) {
      $array[] = $this_value;
    }
  }
  return $array;
}

function snmpwalk_cache_oid($poll_oid, $device, $array, $mib = 0) {
  global $config;
  $cmd  = $config['snmpbulkwalk'] . " -O Qs -" . $device['snmpver'] . " -c " . $device['community'] . " " .
                                    $device['hostname'].":".$device['port'] . " ";
  if($mib) { $cmd .= "-m $mib "; }
  $cmd .= $poll_oid;
  $data = trim(shell_exec($cmd));
  $device_id = $device['device_id'];
  foreach(explode("\n", $data) as $entry) {
    list($oid,$value) = explode("=", $entry);
    $oid = trim($oid); $value = trim($value);
    list($oid, $index) = explode(".", $oid);
    if (!strstr($this_value, "at this OID") && $oid && $index) {
      $array[$device_id][$index][$oid] = $value;
    }
  }
  return $array;
}

function snmpwalk_cache_twopart_oid($oid, $device, $array, $mib = 0) {
  global $config;
  $cmd  = $config['snmpbulkwalk'] . " -O Qs -" . $device['snmpver'] . " -c " . $device['community'] . " " .
                                    $device['hostname'].":".$device['port'] . " ";
  if($mib) { $cmd .= "-m $mib "; }
  $cmd .= $oid;
  $data = trim(shell_exec($cmd));
  $device_id = $device['device_id'];
  foreach(explode("\n", $data) as $entry) {
    list($oid,$value) = explode("=", $entry);
    $oid = trim($oid); $value = trim($value);
    list($oid, $first, $second) = explode(".", $oid);
    if (!strstr($this_value, "at this OID") && $oid && $first && $second) {
      $array[$device_id][$first][$second][$oid] = $value;
    }
  }
  return $array;
}

function snmp_cache_slotport_oid($oid, $device, $array, $mib = 0) {
  global $config;
  $cmd  = $config['snmpbulkwalk'] . " -O Qs -" . $device['snmpver'] . " -c " . $device['community'] . " " . 
                                    $device['hostname'].":".$device['port'] . " ";
  if($mib) { $cmd .= "-m $mib "; }
  $cmd .= $oid;
  $data = trim(shell_exec($cmd));
  $device_id = $device['device_id'];
  foreach(explode("\n", $data) as $entry) {
    $entry = str_replace($oid.".", "", $entry);
    list($slotport, $value) = explode("=", $entry);
    $slotport = trim($slotport); $value = trim($value);
    if ($array[$device_id][$slotport]['ifIndex']) {
      $ifIndex = $array[$device_id][$slotport]['ifIndex'];
      #$array[$device_id][$slotport][$oid] = $value;
      $array[$device_id][$ifIndex][$oid] = $value;
    }
  }
  return $array;
}


function snmp_cache_oid($oid, $device, $array, $mib = 0) {
  global $config;
  $cmd  = $config['snmpbulkwalk'] . " -O UQs -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  if($mib) { $cmd .= "-m $mib "; }
  $cmd .= $oid;
  $data = trim(shell_exec($cmd));
  $device_id = $device['device_id'];
  #echo("Caching: $oid\n");
  foreach(explode("\n", $data) as $entry) {
    list ($this_oid, $this_value) = split("=", $entry);
    list ($this_oid, $this_index) = explode(".", $this_oid);
    $this_index = trim($this_index);
    $this_oid = trim($this_oid);
    $this_value = trim($this_value);
    if(!strstr($this_value, "at this OID") && $this_index) {
      $array[$device_id][$this_index][$this_oid] = $this_value;
    }
    $array[$device_id][$oid] = '1';
  }
  return $array;
}

function snmp_cache_port_oids($oids, $port, $device, $array, $mib=0) {
  global $config;
  foreach($oids as $oid){
    $string .= " $oid.$port";
  }
  $cmd = $config['snmpget'] . " -O vq -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  if($mib) { $cmd .= "-m $mib "; }
  $cmd .= $string;
  $data = trim(shell_exec($cmd));
  $x=0;
  $values = explode("\n", $data);
  #echo("Caching: ifIndex $port\n");
  foreach($oids as $oid){
    if(!strstr($values[$x], "at this OID")) {
      $array[$device[device_id]][$port][$oid] = $values[$x];
    }
    $x++;
  }
  return $array;
}

function snmp_cache_portIfIndex ($device, $array) {
  global $config;
  $cmd = $config['snmpwalk'] . " -CI -m CISCO-STACK-MIB -O q -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " portIfIndex";
  $output = trim(shell_exec($cmd));
  $device_id = $device['device_id'];
  foreach(explode("\n", $output) as $entry){
    $entry = str_replace("CISCO-STACK-MIB::portIfIndex.", "", $entry);
    list($slotport, $ifIndex) = explode(" ", $entry);
    if($slotport && $ifIndex){
      $array[$device_id][$ifIndex]['portIfIndex'] = $slotport;
      $array[$device_id][$slotport]['ifIndex'] = $ifIndex;
    }
  }
  return $array;
}

function snmp_cache_portName ($device, $array) {
  global $config;
  $cmd = $config['snmpwalk'] . " -CI -m CISCO-STACK-MIB -O Qs -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " portName";
  $output = trim(shell_exec($cmd));
  $device_id = $device['device_id'];
  #echo("Caching: portName\n");
  foreach(explode("\n", $output) as $entry){
    $entry = str_replace("portName.", "", $entry);
    list($slotport, $portName) = explode("=", $entry);
    $slotport = trim($slotport); $portName = trim($portName);
    if ($array[$device_id][$slotport]['ifIndex']) {
      $ifIndex = $array[$device_id][$slotport]['ifIndex'];
      $array[$device_id][$slotport]['portName'] = $portName;
      $array[$device_id][$ifIndex]['portName'] = $portName;
    }
  }
  return $array;
}

?>
