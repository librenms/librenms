<?php

function discover_link($local_interface_id, $protocol, $remote_interface_id, $remote_hostname, $remote_port, $remote_platform, $remote_version) {

      global $config; global $debug; global $link_exists;
      if (mysql_result(@mysql_query("SELECT COUNT(*) FROM `links` WHERE `remote_hostname` = '$remote_hostname' AND `local_interface_id` = '$local_interface_id'
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

function discover_processor(&$valid_processor, $device, $oid, $index, $type, $descr, $precision = "1", $current = NULL, $entPhysical = NULL, $hrDevice = NULL) {

      global $config; global $debug; global $valid_processor;
      if($debug) { echo("$device, $oid, $index, $type, $descr, $precision, $current, $entPhysical, $hrDevice\n"); }
      if($descr) {
          if(mysql_result(mysql_query("SELECT count(processor_id) FROM `processors` WHERE `processor_index` = '$index' AND `device_id` = '".$device['device_id']."' AND `processor_type` = '$type'"),0) == '0') {
            $query = "INSERT INTO processors (`entPhysicalIndex`, `device_id`, `processor_descr`, `processor_index`, `processor_oid`, `processor_usage`, `processor_type`)
                      values ('$entPhysicalIndex', '".$device['device_id']."', '$descr', '$index', '$usage_oid', '$usage', '$type')";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
            echo("+");
          } else {
            echo(".");
            $query = "UPDATE `processors` SET `processor_descr` = '".$descr."', `processor_oid` = '".$usage_oid."', `processor_usage` = '".$usage."'
                      WHERE `device_id` = '".$device['device_id']."' AND `processor_index` = '".$index."' AND `processor_type` = '".$type."'";
            mysql_query($query);
            if($debug) { print $query . "\n"; }
          }
          $valid_processor[$type][$index] = 1;
      }
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
