<?php

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
          mysql_query("UPDATE fanspeed SET `fan_descr` = '$descr', `fan_oid` = '$oid', `fan_precision` = '$precision' WHERE `device_id` = '$id' AND fan_type = '$type' AND `fan_index` = '$fan_index' ");
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
        $query .= " VALUES ('".$device['device_id']."', '$oid', '$index', '$type', '$descr', '$precision', '$low_limit', '$volt_limit_low', '$current')";
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
