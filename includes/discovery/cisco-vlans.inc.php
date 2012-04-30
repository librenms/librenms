<?php

if ($device['os_group'] == "cisco")
{
  echo("Cisco VLANs : ");

  $vtpversion_cmd  = $config['snmpget'] . " -M " . $config['mibdir'] . " -m CISCO-VTP-MIB -Oqv -" . $device['snmpver'] . " -c " . $device['community'] . " ";
  $vtpversion_cmd .= $device['hostname'].":".$device['port'] . " .1.3.6.1.4.1.9.9.46.1.1.1.0";
  $vtpversion = trim(`$vtpversion_cmd 2>/dev/null`);

  if ($vtpversion == '1' || $vtpversion == '2' || $vtpversion == '3' || $vtpversion == 'one' || $vtpversion == 'two' || $vtpversion == 'three')
  {
    $vtp_domain_cmd  = $config['snmpget'] . " -M " . $config['mibdir'] . " -m CISCO-VTP-MIB -Oqv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
    $vtp_domain_cmd .= " .1.3.6.1.4.1.9.9.46.1.2.1.1.2.1";
    $vtp_domain = trim(str_replace("\"", "", `$vtp_domain_cmd 2>/dev/null`));

    echo("VTP v$vtpversion $vtp_domain ");

    $vlans_cmd  = $config['snmpwalk'] . " -M " . $config['mibdir'] . " -m CISCO-VTP-MIB -O qn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
    $vlans_cmd .= "1.3.6.1.4.1.9.9.46.1.3.1.1.2.1 | sed s/.1.3.6.1.4.1.9.9.46.1.3.1.1.2.1.//g | cut -f 1 -d\" \"";

    $vlans  = trim(`$vlans_cmd | grep -v o`);

    foreach (explode("\n", $vlans) as $vlan)
    {
      $vlan_descr_cmd  = $config['snmpget'] . " -M " . $config['mibdir'] . " -m CISCO-VTP-MIB -O nvq -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
      $vlan_descr_cmd .= ".1.3.6.1.4.1.9.9.46.1.3.1.1.4.1." . $vlan;
      $vlan_descr = shell_exec($vlan_descr_cmd);

      $vlan_descr = trim(str_replace("\"", "", $vlan_descr));

      if (mysql_result(mysql_query("SELECT COUNT(vlan_id) FROM `vlans` WHERE `device_id` = '" . $device['device_id'] . "' AND `vlan_domain` = '" . $vtp_domain . "' AND `vlan_vlan` = '" . $vlan . "'"), 0) == '0')
      {
        mysql_query("INSERT INTO `vlans` (`device_id`,`vlan_domain`,`vlan_vlan`, `vlan_descr`) VALUES (" . $device['device_id'] . ",'" . mres($vtp_domain) . "','$vlan', '" . mres($vlan_descr) . "')");
        echo("+");
      } else {
        echo(".");
        mysql_query("UPDATE `vlans` SET `vlan_descr`='" . mres($vlan_descr) . "' WHERE `device_id`='" . $device['device_id'] . "' AND `vlan_vlan`='" . $vlan . "' AND `vlan_domain`='" . $vtp_domain . "'");
      }

      $this_vlans[] = $vlan;
    }

  }

  echo("\n");
}

?>
