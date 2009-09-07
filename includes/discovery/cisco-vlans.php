<?php

  echo("Cisco VLANs : ");

  $vtpversion_cmd  = $config['snmpget'] . " -m CISCO-VTP-MIB -Oqv -" . $device['snmpver'] . " -c " . $device['community'] . " ";
  $vtpversion_cmd .= $device['hostname'].":".$device['port'] . " .1.3.6.1.4.1.9.9.46.1.1.1.0";
  $vtpversion = trim(`$vtpversion_cmd 2>/dev/null`);  

  if($vtpversion == '1' || $vtpversion == '2' || $vtpversion == 'two' || $vtpversion == 'three') { 

    $vtp_domain_cmd  = $config['snmpget'] . " -m CISCO-VTP-MIB -Oqv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
    $vtp_domain_cmd .= " .1.3.6.1.4.1.9.9.46.1.2.1.1.2.1";
    $vtp_domain = trim(str_replace("\"", "", `$vtp_domain_cmd 2>/dev/null`));

    echo("VTP v$vtpversion $vtp_domain ");

    $vlans_cmd  = $config['snmpwalk'] . " -m CISCO-VTP-MIB -O qn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
    $vlans_cmd .= "1.3.6.1.4.1.9.9.46.1.3.1.1.2.1 | sed s/.1.3.6.1.4.1.9.9.46.1.3.1.1.2.1.//g | cut -f 1 -d\" \"";

    $vlans  = trim(`$vlans_cmd | grep -v o`);

    foreach(explode("\n", $vlans) as $vlan) {

      $vlan_descr_cmd  = $config['snmpget'] . " -m CISCO-VTP-MIB -O nvq -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " "; 
      $vlan_descr_cmd .= ".1.3.6.1.4.1.9.9.46.1.3.1.1.4.1." . $vlan;
      $vlan_descr = shell_exec($vlan_descr_cmd);

      $vlan_descr = trim(str_replace("\"", "", $vlan_descr));

      if(mysql_result(mysql_query("SELECT COUNT(vlan_id) FROM `vlans` WHERE `device_id` = '" . $device['device_id'] . "' AND `vlan_domain` = '" . $vtp_domain . "' AND `vlan_vlan` = '" . $vlan . "'"), 0) == '0') {
        mysql_query("INSERT INTO `vlans` (`device_id`,`vlan_domain`,`vlan_vlan`, `vlan_descr`) VALUES (" . $device['device_id'] . ",'" . $vtp_domain . "','$vlan', '$vlan_descr')");
        echo("+");
      } else { echo("."); }


      $this_vlans[] = $vlan;

    }

    $device_vlans = mysql_query("SELECT * FROM `vlans` WHERE `device_id` = '" . $device['device_id'] . "' AND `vlan_domain` = '" . $vtp_domain . "'");
    while($dev_vlan = mysql_fetch_array($device_vlans)) {
      unset($vlan_exists);
      foreach($this_vlans as $test_vlan) {
        if($test_vlan == $dev_vlan['vlan_vlan']) { $vlan_exists = 1; }
      }
      if(!$vlan_exists) { 
        mysql_query("DELETE FROM `vlans` WHERE `vlan_id` = '" . $dev_vlan['vlan_id'] . "'"); 
        echo("-");
        #echo("Deleted VLAN ". $dev_vlan['vlan_vlan'] ."\n"); 
      }
    }
  }

  unset($this_vlans);

  echo("\n");
