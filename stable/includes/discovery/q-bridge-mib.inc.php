<?php

echo("Q-BRIDGE-MIB VLANs : ");

$vlanversion = snmp_get($device, "dot1qVlanVersionNumber.0", "-Oqv", "Q-BRIDGE-MIB");

if ($vlanversion == 'version1')
{
  echo("VLAN $vlanversion ");

  $vlans = snmp_walk($device, "dot1qVlanFdbId", "-Oqn", "Q-BRIDGE-MIB");

  $vlan_array = snmpwalk_cache_multi_oid($device, "dot1qVlanStaticName", $vlan_array, "Q-BRIDGE-MIB");

  foreach (explode("\n", $vlans) as $vlan_oid)
  {
    list($oid,$vlan_index) = explode(' ',$vlan_oid);
    $oid_ex = explode('.',$oid);
    $vlan = $oid_ex[count($oid_ex)-1];

    $vlan_descr = trim(str_replace("\"", "", $vlan_array[$vlan]['dot1qVlanStaticName']));

    if (mysql_result(mysql_query("SELECT COUNT(vlan_id) FROM `vlans` WHERE `device_id` = '" . $device['device_id'] . "' AND `vlan_domain` = '' AND `vlan_vlan` = '" . $vlan . "'"), 0) == '0')
    {
      mysql_query("INSERT INTO `vlans` (`device_id`,`vlan_domain`,`vlan_vlan`, `vlan_descr`) VALUES (" . $device['device_id'] . ",'','$vlan', '" . mres($vlan_descr) . "')");
      echo("+");
    } else {
      mysql_query("UPDATE `vlans` SET `vlan_descr`='" . mres($vlan_descr) . "' WHERE `device_id`='" . $device['device_id'] . "' AND `vlan_vlan`='" . $vlan . "'");
      echo(".");
    }

    $this_vlans[] = $vlan;
  }

}

echo("\n");

?>
