<?php

### Discover sensors
function discover_sensor (&$valid, $class, $device, $oid, $index, $type, $descr, $divisor = '1', $multiplier = '1', $low_limit = NULL, $low_warn_limit = NULL, $warn_limit = NULL, $high_limit = NULL, $current = NULL, $poller_type = 'snmp')
{
  global $config, $debug;
  if($debug) { echo("$oid, $index, $type, $descr, $precision\n"); }

  if (mysql_result(mysql_query("SELECT count(sensor_id) FROM `sensors` WHERE poller_type='" . mres($poller_type) . "' AND sensor_class='" . mres($class) . "' AND device_id = '".$device['device_id']."' AND sensor_type = '$type' AND `sensor_index` = '$index'"),0) == '0')
  {

    if(!$high_limit) { $high_limit = sensor_limit($class, $current); }
    if(!$low_limit)  { $low_limit  = sensor_low_limit($class, $current); }

    $query = "INSERT INTO sensors (`poller_type`,`sensor_class`, `device_id`, `sensor_oid`, `sensor_index`, `sensor_type`, `sensor_descr`, `sensor_divisor`, `sensor_multiplier`, `sensor_limit`, `sensor_limit_warn`, `sensor_limit_low`, `sensor_limit_low_warn`, `sensor_current`) ";
    $query .= " VALUES ('" . mres($poller_type) . "','" . mres($class) . "', '".$device['device_id']."', '$oid', '$index', '$type', '$descr', '$divisor', '$multiplier', '$high_limit', '$warn_limit', '$low_limit', '$low_warn_limit', '$current')";
    mysql_query($query);
    if($debug) { echo("$query\n". mysql_affected_rows() . " inserted\n"); }
    echo("+");
    log_event("Sensor Added: ".mres($class)." ".mres($type)." ". mres($index)." ".mres($descr), $device['device_id'], 'sensor', mysql_insert_id());
  }
  else
  {
    $sensor_entry = mysql_fetch_assoc(mysql_query("SELECT * FROM `sensors` WHERE sensor_class='" . mres($class) . "' AND device_id = '".$device['device_id']."' AND sensor_type = '$type' AND `sensor_index` = '$index'"));

    if(!$high_limit)
    { 
      if(!$sensor_entry['sensor_limit'])
      {
        $high_limit  = sensor_limit($class, $current);
      } else {
        $high_limit = $sensor_entry['sensor_limit'];
      }
    }

    if ($high_limit != $sensor_entry['sensor_limit'])
    {
      $query = "UPDATE sensors SET `sensor_limit` = '".$high_limit."' WHERE `sensor_id` = '".$sensor_entry['sensor_id']."'"; 
      mysql_query($query);
      if($debug) { echo("$query\n". mysql_affected_rows() . " updated\n"); }
      echo("H");
      log_event("Sensor High Limit Updated: ".mres($class)." ".mres($type)." ". mres($index)." ".mres($descr)." (".$high_limit.")", $device['device_id'], 'sensor', $sensor_id);
    }

    if(!$low_limit)
    { 
      if(!$sensor_entry['sensor_limit_low'])
      {
        $low_limit  = sensor_low_limit($class, $current); 
      } else {
        $low_limit = $sensor_entry['sensor_limit_low'];
      }
    }

    if ($sensor_entry['sensor_limit_low'] != $low_limit)
    {
      $query = "UPDATE sensors SET `sensor_limit_low` = '".$low_limit."' WHERE `sensor_id` = '".$sensor_entry['sensor_id']."'";
      mysql_query($query); 
      if($debug) { echo("$query\n". mysql_affected_rows() . " updated\n"); }
      echo("L");
      log_event("Sensor Low Limit Updated: ".mres($class)." ".mres($type)." ". mres($index)." ".mres($descr)." (".$low_limit.")", $device['device_id'], 'sensor', $sensor_id);
    }

    if($oid == $sensor_entry['sensor_oid'] && $descr == $sensor_entry['sensor_descr'] && $multiplier == $sensor_entry['sensor_multiplier'] && $divisor == $sensor_entry['sensor_divisor'])
    {
      echo(".");
    }
    else
    {
      $query = "UPDATE sensors SET `sensor_descr` = '$descr', `sensor_oid` = '$oid', `sensor_multiplier` = '$multiplier', `sensor_divisor` = '$divisor' WHERE `sensor_class` = '" . mres($class) . "' AND `device_id` = '" . $device['device_id'] . "' AND sensor_type = '$type' AND `sensor_index` = '$index'";
      mysql_query($query);
      echo("U");
      log_event("Sensor Updated: ".mres($class)." ".mres($type)." ". mres($index)." ".mres($descr), $device['device_id'], 'sensor', $sensor_id);
      if($debug) { echo("$query\n". mysql_affected_rows() . " updated\n"); }
    }
  }
  $valid[$class][$type][$index] = 1;
  return $return;
}

function sensor_low_limit ($class, $current) 
{
  $limit = NULL;

  switch($class) 
  {
    case 'temperature':
     $limit = $current * 0.7; 
     break;
    case 'voltage':
     $limit = $current * 0.85; 
     break;
    case 'humidity':
     $limit = "70"; 
     break;
    case 'freq':
     $limit = $current * 0.95; 
     break;
    case 'current':
     $limit = $current * 0.80; 
     break;
    case 'fanspeed':
     $limit = $current * 0.80; 
     break;
  }
  return $limit;
}


function sensor_limit ($class, $current) 
{
  $limit = NULL;

  switch($class)
  {
    case 'temperature':
     $limit = $current * 1.60; 
     break;
    case 'voltage':
     $limit = $current * 1.15; 
     break;
    case 'humidity':
     $limit = "70"; 
     break;
    case 'freq':
     $limit = $current * 1.05; 
     break;
    case 'current':
     $limit = $current * 1.50; 
     break;
    case 'fanspeed':
     $limit = $current * 1.30; 
     break;
  }
  return $limit;
}

function check_valid_sensors($device, $class, $valid) 
{
  $sql = "SELECT * FROM sensors AS S, devices AS D WHERE S.sensor_class='".$class."' AND S.device_id = D.device_id AND D.device_id = '".$device['device_id']."'";
  if ($query = mysql_query($sql))
  {
    while ($test = mysql_fetch_assoc($query))
    {
      $index = $test['sensor_index'];
      $type = $test['sensor_type'];
      if($debug) { echo($index . " -> " . $type . "\n"); }
      if(!$valid[$class][$type][$index])
      {
        echo("-");
        mysql_query("DELETE FROM `sensors` WHERE sensor_class='".$class."' AND sensor_id = '" . $test['sensor_id'] . "'");
        log_event("Sensor Deleted: ".$test['sensor_class']." ".$test['sensor_type']." ". $test['sensor_index']." ".$test['sensor_descr'], $device['device_id'], 'sensor', $sensor_id);
      }
      unset($oid); unset($type);
    }
  }
}


function discover_juniAtmVp(&$valid, $interface_id, $vp_id, $vp_descr)
{
  global $config, $debug;

  if (mysql_result(mysql_query("SELECT COUNT(*) FROM `juniAtmVp` WHERE `interface_id` = '".$interface_id."' AND `vp_id` = '".$vp_id."'"),0) == "0") 
  {
     $sql = "INSERT INTO `juniAtmVp` (`interface_id`,`vp_id`,`vp_descr`) VALUES ('".$interface_id."','".$vp_id."','".$vp_descr."')";
     mysql_query($sql); echo("+"); 
     if($debug) { echo($sql . " - " . mysql_affected_rows() . "inserted "); }
  } 
  else 
  {
    echo(".");
  }
  $valid[$interface_id][$vp_id] = 1;
}

function discover_link($local_interface_id, $protocol, $remote_interface_id, $remote_hostname, $remote_port, $remote_platform, $remote_version)
{
  global $config, $debug, $link_exists;

  if (mysql_result(mysql_query("SELECT COUNT(*) FROM `links` WHERE `remote_hostname` = '$remote_hostname' AND `local_interface_id` = '$local_interface_id'
                                     AND `protocol` = '$protocol' AND `remote_port` = '$remote_port'"),0) == "0")
  {
    $sql = "INSERT INTO `links` (`local_interface_id`,`protocol`,`remote_interface_id`,`remote_hostname`,`remote_port`,`remote_platform`,`remote_version`)
                             VALUES ('$local_interface_id','$protocol','$remote_interface_id','$remote_hostname','$remote_port','$remote_platform','$remote_version')";
    mysql_query($sql);
    echo("+"); if($debug) { echo("$sql"); }     
  }
  else
  {
    $data = mysql_fetch_assoc(mysql_query("SELECT * FROM `links` WHERE `remote_hostname` = '$remote_hostname' AND `local_interface_id` = '$local_interface_id'
                                               AND `protocol` = '$protocol' AND `remote_port` = '$remote_port'"));
    if($data['remote_interface_id'] == $remote_interface_id && $data['remote_platform'] == $remote_platform && $remote_version == $remote_version)
    {
      echo(".");
    }
    else
    {
      $sql = "UPDATE `links` SET `remote_interface_id` = $remote_interface_id, `remote_platform` = '$remote_platform', `remote_version` = '$remote_version' WHERE `id` = '".$data['id']."'";
      mysql_query($sql); 
      echo("U"); if($debug) {echo("$sql");}
    }
  }
  
  $link_exists[$local_interface_id][$remote_hostname][$remote_port] = 1;
}

function discover_storage(&$valid, $device, $index, $type, $mib, $descr, $size, $units, $used = NULL)
{
  global $config, $debug;
  
  if($debug) { echo("$device, $index, $type, $mib, $descr, $units, $used, $size\n"); }
  if($descr && $size > "0")
  {
    if(mysql_result(mysql_query("SELECT count(storage_id) FROM `storage` WHERE `storage_index` = '$index' AND `device_id` = '".$device['device_id']."' AND `storage_mib` = '$mib'"),0) == '0')
    {
      $query = "INSERT INTO storage (`device_id`, `storage_descr`, `storage_index`, `storage_mib`, `storage_type`, `storage_units`,`storage_size`,`storage_used`)
                      values ('".$device['device_id']."', '$descr', '$index', '$mib','$type', '$units', '$size', '$used')";
      mysql_query($query);
      if($debug) { print $query . "\n"; mysql_error(); }
      echo("+");
    }
    else
    {
      echo(".");
      $query = "UPDATE `storage` SET `storage_descr` = '".$descr."', `storage_type` = '".$type."', `storage_units` = '".$units."', `storage_size` = '".$size."'
                      WHERE `device_id` = '".$device['device_id']."' AND `storage_index` = '".$index."' AND `storage_mib` = '".$mib."'";
      mysql_query($query);
      if($debug) { print $query . "\n"; }
    }
    
    $valid[$mib][$index] = 1;
  }
}


function discover_processor(&$valid, $device, $oid, $index, $type, $descr, $precision = "1", $current = NULL, $entPhysicalIndex = NULL, $hrDeviceIndex = NULL)
{
  global $config, $debug;
  
  if($debug) { echo("$device, $oid, $index, $type, $descr, $precision, $current, $entPhysicalIndex, $hrDeviceIndex\n"); }
  if($descr)
  {
    $descr = str_replace("\"", "", $descr);
    if(mysql_result(mysql_query("SELECT count(processor_id) FROM `processors` WHERE `processor_index` = '$index' AND `device_id` = '".$device['device_id']."' AND `processor_type` = '$type'"),0) == '0')
    {
      $query = "INSERT INTO processors (`entPhysicalIndex`, `hrDeviceIndex`, `device_id`, `processor_descr`, `processor_index`, `processor_oid`, `processor_usage`, `processor_type`, `processor_precision`)
                      values ('$entPhysicalIndex', '$hrDeviceIndex', '".$device['device_id']."', '$descr', '$index', '$oid', '$current', '$type','$precision')";
      mysql_query($query);
      if($debug) { print $query . "\n"; }
      echo("+");
    }
    else
    {
      echo(".");
      $query = "UPDATE `processors` SET `processor_descr` = '".$descr."', `processor_oid` = '".$oid."', `processor_precision` = '".$precision."'
                      WHERE `device_id` = '".$device['device_id']."' AND `processor_index` = '".$index."' AND `processor_type` = '".$type."'";
      mysql_query($query);
      if($debug) { print $query . "\n"; }
    }

    $valid[$type][$index] = 1;
  }
}


function discover_mempool(&$valid, $device, $index, $type, $descr, $precision = "1", $entPhysicalIndex = NULL, $hrDeviceIndex = NULL)
{
  global $config, $debug;
  
  if($debug) { echo("$device, $oid, $index, $type, $descr, $precision, $current, $entPhysicalIndex, $hrDeviceIndex\n"); }
  if($descr)
  {
    if(mysql_result(mysql_query("SELECT count(mempool_id) FROM `mempools` WHERE `mempool_index` = '$index' AND `device_id` = '".$device['device_id']."' AND `mempool_type` = '$type'"),0) == '0')
    {
      $query = "INSERT INTO mempools (`entPhysicalIndex`, `hrDeviceIndex`, `device_id`, `mempool_descr`, `mempool_index`, `mempool_type`, `mempool_precision`)
                      values ('$entPhysicalIndex', '$hrDeviceIndex', '".$device['device_id']."', '$descr', '$index', '$type','$precision')";
      mysql_query($query);
      if($debug) { print $query . "\n"; }
      echo("+");
    }
    else
    {
      echo(".");
      $query  = "UPDATE `mempools` SET `mempool_descr` = '".$descr."', `entPhysicalIndex` = '".$entPhysicalIndex."'";
      $query .= ", `hrDeviceIndex` = '$hrDeviceIndex' ";
      $query .= "WHERE `device_id` = '".$device['device_id']."' AND `mempool_index` = '".$index."' AND `mempool_type` = '".$type."'";
      mysql_query($query);
      if($debug) { print $query . "\n"; }
    }

    $valid[$type][$index] = 1;
  }
}

function discover_toner(&$valid, $device, $oid, $index, $type, $descr, $capacity = NULL, $current = NULL)
{
  global $config, $debug;
  
  if($debug) { echo("$oid, $index, $type, $descr, $capacity\n"); }

  if (mysql_result(mysql_query("SELECT count(toner_id) FROM `toner` WHERE device_id = '".$device['device_id']."' AND toner_type = '$type' AND `toner_index` = '$index'"),0) == '0')
  {
    $query = "INSERT INTO toner (`device_id`, `toner_oid`, `toner_index`, `toner_type`, `toner_descr`, `toner_capacity`, `toner_current`) ";
    $query .= " VALUES ('".$device['device_id']."', '$oid', '$index', '$type', '$descr', '$capacity', '$current')";
    mysql_query($query);
    echo("+");
  } 
  else 
  {
    $toner_entry = mysql_fetch_assoc(mysql_query("SELECT * FROM `toner` WHERE device_id = '".$device['device_id']."' AND toner_type = '$type' AND `toner_index` = '$index'"));
    if($oid == $toner_entry['toner_oid'] && $descr == $toner_entry['toner_descr'] && $capacity == $toner_entry['toner_capacity'])
    {
      echo(".");
    }
    else 
    {
      mysql_query("UPDATE toner SET `toner_descr` = '$descr', `toner_oid` = '$oid', `toner_capacity` = '$capacity' WHERE `device_id` = '".$device['device_id']."' AND toner_type = '$type' AND `toner_index` = '$index' ");
      echo("U");
    }
  }
  
  $valid[$type][$index] = 1;
  return $return;
}

?>
