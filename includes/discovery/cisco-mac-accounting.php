<?

  unset ($mac_table);

  echo("ipnettomedia\n");
  $ipNetToMedia_data = shell_exec($config['snmpbulkwalk'] . " -m IP-MIB -Oq -".$device['snmpver']." -c ".$config['community']." ".$device['hostname']." ipNetToMediaPhysAddress");
  $ipNetToMedia_data = str_replace("ipNetToMediaPhysAddress.", "", trim($ipNetToMedia_data));
  $ipNetToMedia_data = str_replace("IP-MIB::", "", trim($ipNetToMedia_data));
  echo("$ipNetToMedia_data\n");
  echo("done\n");
  foreach(explode("\n", $ipNetToMedia_data) as $data) {
    list($oid, $mac) = explode(" ", $data);
    list($if, $first, $second, $third, $fourth) = explode(".", $oid);
    list($m_a, $m_b, $m_c, $m_d, $m_e, $m_f) = explode(":", $mac); 
    $interface = mysql_fetch_array(mysql_query("SELECT * FROM interfaces WHERE device_id = '".$device['device_id']."' AND ifIndex = '".$if."'"));
    $ip = $first .".". $second .".". $third .".". $fourth;
    $m_a = zeropad($m_a);
    $m_b = zeropad($m_b);
    $m_c = zeropad($m_c);
    $m_d = zeropad($m_d);
    $m_e = zeropad($m_e);
    $m_f = zeropad($m_f);
    $md_a = hexdec($m_a);
    $md_b = hexdec($m_b);
    $md_c = hexdec($m_c);
    $md_d = hexdec($m_d);
    $md_e = hexdec($m_e);
    $md_f = hexdec($m_f);
    $mac = "$m_a:$m_b:$m_c:$m_d:$m_e:$m_f"; 
    $mac_table[$if][$mac]['ip'] =  $ip;
    $mac_table[$if][$mac]['ciscomac'] = "$m_a$m_b.$m_c$m_d.$m_e$m_f";
    $clean_mac = $m_a . $m_b . $m_c . $m_d . $m_e . $m_f;    
    $mac_table[$if][$mac]['cleanmac'] = $clean_mac;
    if(mysql_result(mysql_query("SELECT COUNT(*) from ipv4_mac WHERE interface_id = '".$interface['interface_id']."' AND ipv4_address = '$ip'"),0)) {
      $sql = "UPDATE `ipv4_mac` SET `mac_address` = '$clean_mac' WHERE interface_id = '".$interface['interface_id']."' AND ipv4_address = '$ip'";
      mysql_query($sql);
    } else {
      #echo("Add MAC $mac\n");
      mysql_query("INSERT INTO `ipv4_mac` (interface_id, mac_address, ipv4_address) VALUES ('".$interface['interface_id']."','$clean_mac','$ip')");
    }
  }

  echo("cisco-ip-stat-mib\n");
  $datas = shell_exec($config['snmpbulkwalk'] . " -m CISCO-IP-STAT-MIB -Oqn -".$device['snmpver']." -c ".$config['community']." ".$device['hostname']." cipMacSwitchedBytes");
  $datas = trim($datas);
  echo("$datas\n");
  echo("done\n");
  foreach(explode("\n", $datas) as $data) {
    list($oid) = explode(" ", $data);
    $oid = str_replace(".1.3.6.1.4.1.9.9.84.1.2.1.1.4.", "", $oid);
    list($if, $direction, $a_a, $a_b, $a_c, $a_d, $a_e, $a_f) = explode(".", $oid);
    $oid = "$a_a.$a_b.$a_c.$a_d.$a_e.$a_f";
    unset($interface);
    $interface = mysql_fetch_array(mysql_query("SELECT * FROM interfaces WHERE device_id = '".$device['device_id']."' AND ifIndex = '".$if."'"));
    $ah_a = zeropad(dechex($a_a));
    $ah_b = zeropad(dechex($a_b));
    $ah_c = zeropad(dechex($a_c));
    $ah_d = zeropad(dechex($a_d));
    $ah_e = zeropad(dechex($a_e));
    $ah_f = zeropad(dechex($a_f));
    $mac = "$ah_a:$ah_b:$ah_c:$ah_d:$ah_e:$ah_f";
    $mac_cisco = "$ah_a$ah_b.$ah_c$ah_d.$ah_e$ah_f";
    $mac_cisco = $mac_table[$if][$mac]['ciscomac'];
    $clean_mac = $mac_table[$if][$mac]['cleanmac'];
    $ip = $mac_table[$if][$mac]['ip'];
    if($ip && $interface) {
      $new_mac = str_replace(":", "", $mac);
      echo($interface['ifDescr'] . " ($if) -> $mac ($oid) -> $ip -> $name");
      if(mysql_result(mysql_query("SELECT COUNT(*) from mac_accounting WHERE interface_id = '".$interface['interface_id']."' AND mac = '$clean_mac'"),0)) {
        #$sql = "UPDATE `mac_accounting` SET `mac` = '$clean_mac' WHERE interface_id = '".$interface['interface_id']."' AND `mac` = '$clean_mac'";
        #mysql_query($sql);
        #if(mysql_affected_rows()) { echo("      UPDATED!"); }
        #echo($sql);
      } else {
        #echo("      Not Exists!");
        mysql_query("INSERT INTO `mac_accounting` (interface_id,  mac) VALUES ('".$interface['interface_id']."','$clean_mac')");
      }      
      echo("\n");
    }
  }

?>
