<?php

function discover_juniAtmVp(&$exists, $interface_id, $vp_id, $vp_descr) {

      global $config; global $debug;
      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `juniAtmVp` WHERE `interface_id` = '".$interface_id."' AND `vp_id` = '".$vp_id."'"),0) == "0") 
      {
         $sql = "INSERT INTO `juniAtmVp` (`interface_id`,`vp_id`,`vp_descr`) VALUES ('".$interface_id."','".$vp_id."','".$vp_descr."')";
         mysql_query($sql); echo("+"); if($debug) { echo($sql . " - " . mysql_affected_rows() . "inserted "); }
      } else {
        echo(".");
      }

      $exists[$interface_id][$vp_id] = 1;

}

function discover_link($local_interface_id, $protocol, $remote_interface_id, $remote_hostname, $remote_port, $remote_platform, $remote_version) {

      global $config; global $debug; global $link_exists;
      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `links` WHERE `remote_hostname` = '$remote_hostname' AND `local_interface_id` = '$local_interface_id'
                                     AND `protocol` = '$protocol' AND `remote_port` = '$remote_port'"),0) == "0")
      {
        $sql = "INSERT INTO `links` (`local_interface_id`,`protocol`,`remote_interface_id`,`remote_hostname`,`remote_port`,`remote_platform`,`remote_version`)
                             VALUES ('$local_interface_id','$protocol','$remote_interface_id','$remote_hostname','$remote_port','$remote_platform','$remote_version')";
        mysql_query($sql);
        echo("+"); if($debug) {echo("$sql");}
      } else {
        $data = mysql_fetch_array(mysql_query("SELECT * FROM `links` WHERE `remote_hostname` = '$remote_hostname' AND `local_interface_id` = '$local_interface_id'
                                               AND `protocol` = '$protocol' AND `remote_port` = '$remote_port'"));
        if($data['remote_interface_id'] == $remote_interface_id && $data['remote_platform'] == $remote_platform && $remote_version == $remote_version)
        {
          echo(".");
        } else {
          $sql = "UPDATE `links` SET `remote_interface_id` = $remote_interface_id, `remote_platform` = '$remote_platform', `remote_version` = '$remote_version' WHERE `id` = '".$data['id']."'";
          mysql_query($sql); 
          echo("U"); if($debug) {echo("$sql");}
        }
      }
      $link_exists[$local_interface_id][$remote_hostname][$remote_port] = 1;

}

function discover_storage(&$valid_storage, $device, $index, $type, $mib, $descr, $size, $units, $used = NULL) {

      global $config; global $debug;
      if($debug) { echo("$device, $index, $type, $mib, $descr, $units, $used, $size\n"); }
      if($descr && $size > "0") {
          if(mysql_result(mysql_query("SELECT count(storage_id) FROM `storage` WHERE `storage_index` = '$index' AND `device_id` = '".$device['device_id']."' AND `storage_mib` = '$mib'"),0) == '0') {
            $query = "INSERT INTO storage (`device_id`, `storage_descr`, `storage_index`, `storage_mib`, `storage_type`, `storage_units`,`storage_size`,`storage_used`)
                      values ('".$device['device_id']."', '$descr', '$index', '$mib','$type', '$units', '$size', '$used')";
            mysql_query($query);
            if($debug) { print $query . "\n"; mysql_error(); }
            echo("+");
          } else {
            echo(".");
            $query = "UPDATE `storage` SET `storage_descr` = '".$descr."', `storage_type` = '".$type."', `storage_units` = '".$units."', `storage_size` = '".$size."'
                      WHERE `device_id` = '".$device['device_id']."' AND `storage_index` = '".$index."' AND `storage_mib` = '".$mib."'";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
          }
          $valid_storage[$mib][$index] = 1;
      }
}


function discover_processor(&$valid_processor, $device, $oid, $index, $type, $descr, $precision = "1", $current = NULL, $entPhysicalIndex = NULL, $hrDeviceIndex = NULL) {

      global $config; global $debug;
      if($debug) { echo("$device, $oid, $index, $type, $descr, $precision, $current, $entPhysicalIndex, $hrDeviceIndex\n"); }
      if($descr) {
          $descr = str_replace("\"", "", $descr);
          if(mysql_result(mysql_query("SELECT count(processor_id) FROM `processors` WHERE `processor_index` = '$index' AND `device_id` = '".$device['device_id']."' AND `processor_type` = '$type'"),0) == '0') {
            $query = "INSERT INTO processors (`entPhysicalIndex`, `hrDeviceIndex`, `device_id`, `processor_descr`, `processor_index`, `processor_oid`, `processor_usage`, `processor_type`, `processor_precision`)
                      values ('$entPhysicalIndex', '$hrDeviceIndex', '".$device['device_id']."', '$descr', '$index', '$oid', '$current', '$type','$precision')";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
            echo("+");
          } else {
            echo(".");
            $query = "UPDATE `processors` SET `processor_descr` = '".$descr."', `processor_oid` = '".$oid."', `processor_usage` = '".$current."'
                      WHERE `device_id` = '".$device['device_id']."' AND `processor_index` = '".$index."' AND `processor_type` = '".$type."'";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
          }
          $valid_processor[$type][$index] = 1;
      }
}


function discover_mempool(&$valid_mempool, $device, $index, $type, $descr, $precision = "1", $entPhysicalIndex = NULL, $hrDeviceIndex = NULL) {

      global $config; global $debug;
      if($debug) { echo("$device, $oid, $index, $type, $descr, $precision, $current, $entPhysicalIndex, $hrDeviceIndex\n"); }
      if($descr) {
          if(mysql_result(mysql_query("SELECT count(mempool_id) FROM `mempools` WHERE `mempool_index` = '$index' AND `device_id` = '".$device['device_id']."' AND `mempool_type` = '$type'"),0) == '0') {
            $query = "INSERT INTO mempools (`entPhysicalIndex`, `hrDeviceIndex`, `device_id`, `mempool_descr`, `mempool_index`, `mempool_type`, `mempool_precision`)
                      values ('$entPhysicalIndex', '$hrDeviceIndex', '".$device['device_id']."', '$descr', '$index', '$type','$precision')";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
            echo("+");
          } else {
            echo(".");
            $query = "UPDATE `mempools` SET `mempool_descr` = '".$descr."' WHERE `device_id` = '".$device['device_id']."' AND `mempool_index` = '".$index."' AND `mempool_type` = '".$type."'";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
          }
          $valid_mempool[$type][$index] = 1;
      }
}

function discover_temperature(&$valid_temp, $device, $oid, $index, $type, $descr, $precision = 1, $low_limit = NULL, $high_limit = NULL, $current)
{
     global $config; global $debug;
     if($debug) { echo("$oid, $index, $type, $descr, $precision\n"); }
     if (mysql_result(mysql_query("SELECT COUNT(temp_id) FROM `temperature` WHERE temp_type = '$type' AND temp_index = '$index' AND device_id = '".$device['device_id']."'"),0) == '0')
     {
        $query  = "INSERT INTO temperature (`device_id`, `temp_type`,`temp_index`,`temp_oid`, `temp_descr`, `temp_limit`, `temp_current`, `temp_precision`)";
        $query .= " values ('".$device['device_id']."', '$type','$index','$oid', '$descr','" . ($high_limit ? $high_limit : $config['defaults']['temp_limit']) . "', '$current', '$precision')";
        mysql_query($query);
        echo("+");
     } else {
        $entry = mysql_fetch_array(mysql_query("SELECT * FROM `temperature` WHERE device_id = '".$device['device_id']."' AND `temp_type` = '$type' AND `temp_index` = '$index'"));
         echo(mysql_error());
        if($oid == $entry['temp_oid'] && $descr == $entry['temp_descr'] && $precision == $ntry['temp_precision']) {
          echo(".");
        } else {
          mysql_query("UPDATE temperature SET `temp_descr` = '$descr', `temp_oid` = '$oid', `temp_precision` = '$precision' WHERE `temp_id` = '".$entry['temp_id']."'");
          echo("U");
        }
      }
      echo(mysql_error());
      $valid_temp[$type][$index] = 1;
      return $return;
}

function discover_fan($device, $oid, $index, $type, $descr, $precision = 1, $low_limit = NULL, $high_limit = NULL, $current = NULL) {

      global $config; global $debug;
      if($debug) { echo("$oid, $index, $type, $descr, $precision\n"); }
      if(!$low_limit) { $low_limit = $config['limit']['fan']; }
      if (mysql_result(mysql_query("SELECT count(fan_id) FROM `fanspeed` WHERE device_id = '".$device['device_id']."' AND fan_type = '$type' AND `fan_index` = '$index'"),0) == '0')
      {
        $query = "INSERT INTO fanspeed (`device_id`, `fan_oid`, `fan_index`, `fan_type`, `fan_descr`, `fan_precision`, `fan_limit`, `fan_current`) ";
        $query .= " VALUES ('".$device['device_id']."', '$oid', '$index', '$type', '$descr', '$precision', '$low_limit', '$current')";
        mysql_query($query);
        echo("+");
      } else {
        $fan_entry = mysql_fetch_array(mysql_query("SELECT * FROM `fanspeed` WHERE device_id = '".$device['device_id']."' AND fan_type = '$type' AND `fan_index` = '$index'"));
        if($oid == $fan_entry['fan_oid'] && $descr == $fan_entry['fan_descr'] && $precision == $fan_entry['fan_precision']) {
          echo(".");
        } else {
          mysql_query("UPDATE fanspeed SET `fan_descr` = '$descr', `fan_oid` = '$oid', `fan_precision` = '$precision' WHERE `device_id` = '".$device['device_id']."' AND fan_type = '$type' AND `fan_index` = '$fan_index' ");
          echo("U");
        }
      }
      return $return;
}

function discover_volt($device, $oid, $index, $type, $descr, $precision = 1, $low_limit = NULL, $high_limit = NULL, $current = NULL) {

      global $config; global $debug;
      if($debug) { echo("$oid, $index, $type, $descr, $precision\n"); }
      if(!$low_limit) { $low_limit = $config['limit']['volt']; }
      if (mysql_result(mysql_query("SELECT count(volt_id) FROM `voltage` WHERE device_id = '".$device['device_id']."' AND volt_type = '$type' AND `volt_index` = '$index'"),0) == '0')
      {
        $query = "INSERT INTO voltage (`device_id`, `volt_oid`, `volt_index`, `volt_type`, `volt_descr`, `volt_precision`, `volt_limit`, `volt_limit_low`, `volt_current`) ";
        $query .= " VALUES ('".$device['device_id']."', '$oid', '$index', '$type', '$descr', '$precision', '$high_limit', '$low_limit', '$current')";
        mysql_query($query);
        if($debug) { echo("$query ". mysql_affected_rows() . " inserted"); }
        echo("+");
      } else {
        $volt_entry = mysql_fetch_array(mysql_query("SELECT * FROM `voltage` WHERE device_id = '".$device['device_id']."' AND volt_type = '$type' AND `volt_index` = '$index'"));
        if($oid == $volt_entry['volt_oid'] && $descr == $volt_entry['volt_descr'] && $precision == $volt_entry['volt_precision']) {
          echo(".");
        } else {
          mysql_query("UPDATE voltage SET `volt_descr` = '$descr', `volt_oid` = '$oid', `volt_precision` = '$precision' WHERE `device_id` = '$id' AND volt_type = '$type' AND `volt_index` = '$volt_index' ");
          echo("U");
	  if($debug) { echo("$query ". mysql_affected_rows() . " updated"); }
        }
      }
      return $return;
}



?>
