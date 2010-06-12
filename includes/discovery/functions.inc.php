<?php


function discover_juniAtmVp(&$exists, $interface_id, $vp_id, $vp_descr)
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
  $exists[$interface_id][$vp_id] = 1;
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
    $data = mysql_fetch_array(mysql_query("SELECT * FROM `links` WHERE `remote_hostname` = '$remote_hostname' AND `local_interface_id` = '$local_interface_id'
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

function discover_storage(&$valid_storage, $device, $index, $type, $mib, $descr, $size, $units, $used = NULL)
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
    
    $valid_storage[$mib][$index] = 1;
  }
}


function discover_processor(&$valid_processor, $device, $oid, $index, $type, $descr, $precision = "1", $current = NULL, $entPhysicalIndex = NULL, $hrDeviceIndex = NULL)
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
      $query = "UPDATE `processors` SET `processor_descr` = '".$descr."', `processor_oid` = '".$oid."', `processor_usage` = '".$current."'
                      WHERE `device_id` = '".$device['device_id']."' AND `processor_index` = '".$index."' AND `processor_type` = '".$type."'";
      mysql_query($query);
      if($debug) { print $query . "\n"; }
    }

    $valid_processor[$type][$index] = 1;
  }
}


function discover_mempool(&$valid_mempool, $device, $index, $type, $descr, $precision = "1", $entPhysicalIndex = NULL, $hrDeviceIndex = NULL)
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
#      entry = mysql_fetch_assoc(mysql_query());
      $query  = "UPDATE `mempools` SET `mempool_descr` = '".$descr."', `entPhysicalIndex` = '".$entPhysicalIndex."'";
      $query .= ", `hrDeviceIndex` = '$hrDeviceIndex' ";
      $query .= "WHERE `device_id` = '".$device['device_id']."' AND `mempool_index` = '".$index."' AND `mempool_type` = '".$type."'";
      mysql_query($query);
      if($debug) { print $query . "\n"; }
    }

    $valid_mempool[$type][$index] = 1;
  }
}

function discover_temperature(&$valid_temp, $device, $oid, $index, $type, $descr, $precision = 1, $low_limit = NULL, $high_limit = NULL, $current)
{
  global $config, $debug; 
  if($debug) { echo("$oid, $index, $type, $descr, $precision, $current\n"); }


  if (mysql_result(mysql_query("SELECT COUNT(temp_id) FROM `temperature` WHERE temp_type = '$type' AND temp_index = '$index' AND device_id = '".$device['device_id']."'"),0) == '0')
  {
    $query  = "INSERT INTO temperature (`device_id`, `temp_type`,`temp_index`,`temp_oid`, `temp_descr`, `temp_limit`, `temp_current`, `temp_precision`)";
    $query .= " values ('".$device['device_id']."', '$type','$index','$oid', '$descr','" . ($high_limit ? $high_limit : $config['defaults']['temp_limit']) . "', '$current', '$precision')";
    mysql_query($query);
    echo("+");
  }
  else
  {
    $entry = mysql_fetch_array(mysql_query("SELECT * FROM `temperature` WHERE device_id = '".$device['device_id']."' AND `temp_type` = '$type' AND `temp_index` = '$index'"));
    echo(mysql_error());
    if($oid == $entry['temp_oid'] && $descr == $entry['temp_descr'] && $precision == $entry['temp_precision'])
    {
      echo(".");
    }
    else
    {
      mysql_query("UPDATE temperature SET `temp_descr` = '$descr', `temp_oid` = '$oid', `temp_precision` = '$precision' WHERE `temp_id` = '".$entry['temp_id']."'");
      echo("U");
    }
  }
  $valid_temp[$type][$index] = 1;
  return $return;
}

function discover_fan($device, $oid, $index, $type, $descr, $precision = 1, $low_limit = NULL, $high_limit = NULL, $current = NULL)
{
  global $config, $debug;
  
  if($debug) { echo("$oid, $index, $type, $descr, $precision\n"); }
  
  if(!$low_limit)
  {
    $low_limit = $config['limit']['fan'];
  }

  if (mysql_result(mysql_query("SELECT count(fan_id) FROM `fanspeed` WHERE device_id = '".$device['device_id']."' AND fan_type = '$type' AND `fan_index` = '$index'"),0) == '0')
  {
    $query = "INSERT INTO fanspeed (`device_id`, `fan_oid`, `fan_index`, `fan_type`, `fan_descr`, `fan_precision`, `fan_limit`, `fan_current`) ";
    $query .= " VALUES ('".$device['device_id']."', '$oid', '$index', '$type', '$descr', '$precision', '$low_limit', '$current')";
    mysql_query($query);
    echo("+");
  }
  else
  {
    $fan_entry = mysql_fetch_array(mysql_query("SELECT * FROM `fanspeed` WHERE device_id = '".$device['device_id']."' AND fan_type = '$type' AND `fan_index` = '$index'"));
    if($oid == $fan_entry['fan_oid'] && $descr == $fan_entry['fan_descr'] && $precision == $fan_entry['fan_precision'])
    {
      echo(".");
    }
    else
    {
      mysql_query("UPDATE fanspeed SET `fan_descr` = '$descr', `fan_oid` = '$oid', `fan_precision` = '$precision' WHERE `device_id` = '".$device['device_id']."' AND fan_type = '$type' AND `fan_index` = '$index' ");
      echo("U");
    }
  }

  return $return;
}

function discover_volt($device, $oid, $index, $type, $descr, $precision = 1, $low_limit = NULL, $high_limit = NULL, $current = NULL)
{
  global $config, $debug;
  
  if($debug) { echo("$oid, $index, $type, $descr, $precision\n"); }
  if(!$low_limit)
  {
    $low_limit = $config['limit']['volt'];
  }
  
  if (mysql_result(mysql_query("SELECT count(volt_id) FROM `voltage` WHERE device_id = '".$device['device_id']."' AND volt_type = '$type' AND `volt_index` = '$index'"),0) == '0')
  {

    if(!$high_limit && isset($current)) { $high_limit = round($current * 1.05, 2); }
    if(!$low_limit && isset($current))  { $low_limit = round($current * 0.95, 2); }

    $query = "INSERT INTO voltage (`device_id`, `volt_oid`, `volt_index`, `volt_type`, `volt_descr`, `volt_precision`, `volt_limit`, `volt_limit_low`, `volt_current`) ";
    $query .= " VALUES ('".$device['device_id']."', '$oid', '$index', '$type', '$descr', '$precision', '$high_limit', '$low_limit', '$current')";
    mysql_query($query);
    if($debug) { echo("$query ". mysql_affected_rows() . " inserted"); }
    echo("+");
  }
  else
  {

    $volt_entry = mysql_fetch_array(mysql_query("SELECT * FROM `voltage` WHERE device_id = '".$device['device_id']."' AND volt_type = '$type' AND `volt_index` = '$index'"));

    if(!isset($current) && isset($volt_entry['current'])) { $current = $volt_entry['current']; }


    if(!$high_limit && !$volt_entry['volt_limit'] && $current)  { $high_limit = round($current * 1.05, 2); } elseif (!$high_limit && $volt_entry['volt_limit']) { $high_limit = $volt_entry['volt_limit']; }
    if(!$low_limit && !$volt_entry['volt_limit_low'] && $current)   { $low_limit = round($current * 0.95, 2); } elseif (!$low_limit && $volt_entry['volt_limit_low']) { $low_limit = $volt_entry['volt_limit_low']; }

    if($oid == $volt_entry['volt_oid'] && $descr == $volt_entry['volt_descr'] && $precision == $volt_entry['volt_precision'] && $volt_entry['volt_limit'] == $high_limit && $volt_entry['volt_limit_low'] == $low_limit)
    {
      echo(".");
    }
    else
    {
      $sql = "UPDATE voltage SET `volt_descr` = '$descr', `volt_oid` = '$oid', `volt_precision` = '$precision', `volt_limit_low` = '$low_limit', `volt_limit` = '$high_limit' WHERE `device_id` = '" . $device['device_id'] . "' AND volt_type = '$type' AND `volt_index` = '$index'";
      $query = mysql_query($sql);
      echo("U");
      if($debug) { echo("$sql ". mysql_affected_rows() . " updated"); }
    }
  }

  return $return;
}

function discover_freq($device, $oid, $index, $type, $descr, $precision = 1, $low_limit = NULL, $high_limit = NULL, $current = NULL)
{
  global $config, $debug;
  
  if($debug) { echo("$oid, $index, $type, $descr, $precision\n"); }
  if(!$low_limit)
  {
    $low_limit = $config['limit']['freq'];
  }
  
  if (mysql_result(mysql_query("SELECT count(freq_id) FROM `frequency` WHERE device_id = '".$device['device_id']."' AND freq_type = '$type' AND `freq_index` = '$index'"),0) == '0')
  {
    $query = "INSERT INTO frequency (`device_id`, `freq_oid`, `freq_index`, `freq_type`, `freq_descr`, `freq_precision`, `freq_limit`, `freq_limit_low`, `freq_current`) ";
    $query .= " VALUES ('".$device['device_id']."', '$oid', '$index', '$type', '$descr', '$precision', '$high_limit', '$low_limit', '$current')";
    mysql_query($query);
    if($debug) { echo("$query ". mysql_affected_rows() . " inserted"); }
    echo("+");
  }
  else
  {
    $freq_entry = mysql_fetch_array(mysql_query("SELECT * FROM `frequency` WHERE device_id = '".$device['device_id']."' AND freq_type = '$type' AND `freq_index` = '$index'"));
    if($oid == $freq_entry['freq_oid'] && $descr == $freq_entry['freq_descr'] && $precision == $freq_entry['freq_precision'])
    {
      echo(".");
    }
    else
    {
      mysql_query("UPDATE frequency SET `freq_descr` = '$descr', `freq_oid` = '$oid', `freq_precision` = '$precision' WHERE `device_id` = '" . $device['device_id'] . "' AND freq_type = '$type' AND `freq_index` = '$index' ");
      echo("U");
      if($debug) { echo("$query ". mysql_affected_rows() . " updated"); }
    }
  }

  return $return;
}

function discover_current($device, $oid, $index, $type, $descr, $precision = 1, $low_limit = NULL, $warn_limit = NULL, $high_limit = NULL, $current = NULL)
{
  global $config, $debug;
  
  if($debug) { echo("$oid, $index, $type, $descr, $precision\n"); }
  if(!$low_limit)
  {
    $low_limit = $config['limit']['current'];
  }
  
  if (mysql_result(mysql_query("SELECT count(current_id) FROM `current` WHERE device_id = '".$device['device_id']."' AND current_type = '$type' AND `current_index` = '$index'"),0) == '0')
  {
    $query = "INSERT INTO current (`device_id`, `current_oid`, `current_index`, `current_type`, `current_descr`, `current_precision`, `current_limit`, `current_limit_warn`, `current_limit_low`, `current_current`) ";
    $query .= " VALUES ('".$device['device_id']."', '$oid', '$index', '$type', '$descr', '$precision', '$high_limit', '$warn_limit', '$low_limit', '$current')";
    mysql_query($query);
    if($debug) { echo("$query ". mysql_affected_rows() . " inserted"); }
    echo("+");
  }
  else
  {
    $current_entry = mysql_fetch_array(mysql_query("SELECT * FROM `current` WHERE device_id = '".$device['device_id']."' AND current_type = '$type' AND `current_index` = '$index'"));
    if($oid == $current_entry['current_oid'] && $descr == $current_entry['current_descr'] && $precision == $current_entry['current_precision'])
    {
      echo(".");
    }
    else
    {
      mysql_query("UPDATE current SET `current_descr` = '$descr', `current_oid` = '$oid', `current_precision` = '$precision' WHERE `device_id` = '" . $device['device_id'] . "' AND current_type = '$type' AND `current_index` = '$index' ");
      echo("U");
      if($debug) { echo("$query ". mysql_affected_rows() . " updated"); }
    }
  }

  return $return;
}

function discover_toner($device, $oid, $index, $type, $descr, $capacity = NULL, $current = NULL)
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
    $toner_entry = mysql_fetch_array(mysql_query("SELECT * FROM `toner` WHERE device_id = '".$device['device_id']."' AND toner_type = '$type' AND `toner_index` = '$index'"));
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
  
  return $return;
}

?>
