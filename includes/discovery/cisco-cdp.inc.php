<?php

  $community = $device['community'];

  echo("CISCO-CDP-MIB: ");

   
  unset($cdp_array);
  $cdp_array = snmpwalk_cache_twopart_oid("cdpCache", $device, $cdp_array, "CISCO-CDP-MIB");

  $cdp_array = $cdp_array[$device[device_id]];

  unset($cdp_links);
  foreach( array_keys($cdp_array) as $key) { 
    $interface = mysql_fetch_array(mysql_query("SELECT * FROM `interfaces` WHERE device_id = '".$device['device_id']."' AND `ifIndex` = '".$key."'"));
    $cdp_if_array = $cdp_array[$key]; 
    foreach( array_keys($cdp_if_array) as $entry_key) {
      $cdp_entry_array = $cdp_if_array[$entry_key];
      if($device['hostname'] && $interface['ifDescr'] && $cdp_entry_array['cdpCacheDeviceId'] && $cdp_entry_array['cdpCacheDevicePort']){
        $cdp_links .= $device['hostname'] . "," . $interface['ifDescr'] . "," . $cdp_entry_array['cdpCacheDeviceId'] . "," . $cdp_entry_array['cdpCacheDevicePort'] . "\n";
      }
    }    
  }

  #echo("$cdp_links");

  foreach ( explode("\n" ,$cdp_links) as $link ) {
    if ($link == "") { break; }
    list($src_host,$src_if, $dst_host, $dst_if) = explode(",", $link);
    $dst_host = strtolower($dst_host);
    $dst_if = strtolower($dst_if);
    $src_host = strtolower($src_host);
    $src_if = strtolower($src_if);
    $ip = gethostbyname($dst_host);
    if ( match_network($config['nets'], $ip) ) {
      if ( mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `sysName` = '$dst_host'"), 0) == '0' ) {
        if($config['cdp_autocreate']) {
          echo("++ Creating: $dst_host \n");
          createHost ($dst_host, $community, "v2c");
        }
      } else {
        echo(".");
      }
    } else {
      echo("!($dst_host)");
    }

    if ( mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `sysName` = '$dst_host'"), 0) == '1' && 
      mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `hostname` = '$src_host'"), 0) == '1' &&
      mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces` AS I, `devices` AS D WHERE `ifDescr` = '$dst_if' AND sysName = '$dst_host' AND D.device_id = I.device_id"), 0) == '1' && 
      mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces` AS I, `devices` AS D WHERE `ifDescr` = '$src_if' AND hostname = '$src_host' AND D.device_id = I.device_id"), 0) == '1')
    {
      $dst_if_id   = mysql_result(mysql_query("SELECT I.interface_id FROM `interfaces` AS I, `devices` AS D WHERE `ifDescr` = '$dst_if' AND sysName = '$dst_host' AND D.device_id = I.device_id"), 0);
      $src_if_id   = mysql_result(mysql_query("SELECT I.interface_id FROM `interfaces` AS I, `devices` AS D WHERE `ifDescr` = '$src_if' AND hostname = '$src_host' AND D.device_id = I.device_id"), 0);
      $linkalive[] = $src_if_id . "," . $dst_if_id;
      if ( mysql_result(mysql_query("SELECT COUNT(*) FROM `links` WHERE `dst_if` = '$dst_if_id' AND `src_if` = '$src_if_id'"),0) == '0') 
      { 
        $sql = "INSERT INTO `links` (`src_if`, `dst_if`, `cdp`) VALUES ('$src_if_id', '$dst_if_id', '1')";
        mysql_query($sql);
        echo("\n++($src_host $src_if -> $dst_host $dst_if)");
      } else { 
        echo(".."); 
      }
    } else {

    } 
  }
  echo("\n");
?>
