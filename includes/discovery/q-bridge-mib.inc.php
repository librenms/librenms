<?php

  echo("Q-BRIDGE-MIB VLANs : ");

  $vlanversion_cmd  = $config['snmpget'] . " -m Q-BRIDGE-MIB -Oqv -" . $device['snmpver'] . " -c " . $device['community'] . " ";
  $vlanversion_cmd .= $device['hostname'].":".$device['port'] . " Q-BRIDGE-MIB::dot1qVlanVersionNumber.0";
  $vlanversion = trim(`$vlanversion_cmd 2>/dev/null`);  

  if($vlanversion == 'version1') {

    echo("VLAN $vlanversion ");

    $vlans_cmd  = $config['snmpwalk'] . " -m Q-BRIDGE-MIB -O qn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
    $vlans_cmd .= "dot1qVlanFdbId";

    $vlans  = trim(`$vlans_cmd | grep -v o`);

    foreach(explode("\n", $vlans) as $vlan_oid) {

      list($oid,$vlan) = split(' ',$vlan_oid);
      $oid_ex = explode('.',$oid);
      $oid = $oid_ex[count($oid_ex)-1];

      $vlan_descr_cmd  = $config['snmpget'] . " -m Q-BRIDGE-MIB -O nvq -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " "; 
      $vlan_descr_cmd .= "dot1qVlanStaticName.$oid|grep -v \"No Such Instance currently exists at this OID\"";
      $vlan_descr = shell_exec($vlan_descr_cmd);

      $vlan_descr = trim(str_replace("\"", "", $vlan_descr));

      if(mysql_result(mysql_query("SELECT COUNT(vlan_id) FROM `vlans` WHERE `device_id` = '" . $device['device_id'] . "' AND `vlan_domain` = '' AND `vlan_vlan` = '" . $vlan . "'"), 0) == '0') {
        mysql_query("INSERT INTO `vlans` (`device_id`,`vlan_domain`,`vlan_vlan`, `vlan_descr`) VALUES (" . $device['device_id'] . ",'','$vlan', '" . mres($vlan_descr) . "')");
        echo("+");
      } else { 
        mysql_query("UPDATE `vlans` SET `vlan_descr`='" . mres($vlan_descr) . "' WHERE `device_id`='" . $device['device_id'] . "' AND `vlan_vlan`='" . $vlan . "'");
        echo("."); 
      }

      $this_vlans[] = $vlan;

    }

    $device_vlans = mysql_query("SELECT * FROM `vlans` WHERE `device_id` = '" . $device['device_id'] . "'");
    while($dev_vlan = mysql_fetch_array($device_vlans)) {
      unset($vlan_exists);
      foreach($this_vlans as $test_vlan) {
        if($test_vlan == $dev_vlan['vlan_vlan']) { $vlan_exists = 1; }
      }
      if(!$vlan_exists) { 
        mysql_query("DELETE FROM `vlans` WHERE `vlan_id` = '" . $dev_vlan['vlan_id'] . "'"); 
        echo("-");
        if ($debug) { echo("Deleted VLAN ". $dev_vlan['vlan_vlan'] ."\n"); }
      }
    }
  }

  unset($this_vlans);

  echo("\n");
