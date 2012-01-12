<?php

if ($config['enable_vrfs'])
{
  if ($device['os_group'] == "cisco" || $device['os_group'] == "junos" || $device['os'] == "ironware")
  {
    unset($vrf_count);

    echo("VRFs : ");

    $oid_cmd = $config['snmpwalk'] . " -M " . $config['mibdir'] . " -m MPLS-VPN-MIB -CI -Ln -Osqn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " mplsVpnVrfRouteDistinguisher";
    $oids = shell_exec($oid_cmd);

    if ($debug) { echo("$oid_cmd -> $oids \n"); }

    $oids = str_replace(".1.3.6.1.3.118.1.2.2.1.3.", "", $oids);
    $oids = str_replace(" \"", "||", $oids);
    $oids = str_replace("\"", "", $oids);

    $oids = trim($oids);
    foreach (explode("\n", $oids) as $oid)
    {
      if ($oid)
      {
        list($vrf['oid'], $vrf['mplsVpnVrfRouteDistinguisher']) = explode("||", $oid);
        $vrf['name'] = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -m MPLS-VPN-MIB -Ln -Osq -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " mplsVpnVrfRouteDistinguisher.".$vrf['oid']));
        list(,$vrf['name'],, $vrf['mplsVpnVrfRouteDistinguisher']) = explode("\"", $vrf['name']);
        $vrf['mplsVpnVrfDescription'] = trim(shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -m MPLS-VPN-MIB -Ln -Osqvn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " mplsVpnVrfDescription.".$vrf['oid']));

        if (@mysql_result(mysql_query("SELECT count(*) FROM vrfs WHERE `device_id` = '".$device['device_id']."'
                                 AND `vrf_oid`='".$vrf['oid']."'"),0))
        {
          $update_query  = "UPDATE `vrfs` SET `mplsVpnVrfDescription` = '".$vrf['mplsVpnVrfDescription']."', `mplsVpnVrfRouteDistinguisher` = '".$vrf['mplsVpnVrfRouteDistinguisher']."' ";
          $update_query .= "WHERE device_id = '".$device['device_id']."' AND vrf_oid = '".$vrf['oid']."'";
          mysql_query($update_query);
        }
        else
        {
          $insert_query  = "INSERT INTO `vrfs` (`vrf_oid`,`vrf_name`,`mplsVpnVrfRouteDistinguisher`,`mplsVpnVrfDescription`,`device_id`) ";
          $insert_query .= "VALUES ('".$vrf['oid']."','".$vrf['name']."','".$vrf['mplsVpnVrfRouteDistinguisher']."','".$vrf['mplsVpnVrfDescription']."','".$device['device_id']."')";
          mysql_query($insert_query);
        }

        $vrf_id = @mysql_result(mysql_query("SELECT vrf_id FROM vrfs WHERE `device_id` = '".$device['device_id']."' AND `vrf_oid`='".$vrf['oid']."'"),0);
        $valid_vrf[$vrf_id] = 1;
        echo("\nRD:".$vrf['mplsVpnVrfRouteDistinguisher']." ".$vrf['name']." ".$vrf['mplsVpnVrfDescription']." ");
        $ports_oid = ".1.3.6.1.3.118.1.2.1.1.2." . $vrf['oid'];
        $ports = shell_exec($config['snmpwalk'] . " -M " . $config['mibdir'] . " -m MPLS-VPN-MIB -CI -Ln -Osqn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " $ports_oid");
        $ports = trim(str_replace($ports_oid . ".", "", $ports));
#       list($ports) = explode(" ", $ports);
        echo(" ( ");
        foreach (explode("\n", $ports) as $if_id)
        {
          $interface = mysql_fetch_assoc(mysql_query("SELECT * FROM ports WHERE ifIndex = '$if_id' AND device_id = '" . $device['device_id'] . "'"));
          echo(makeshortif($interface['ifDescr']) . " ");
          mysql_query("UPDATE ports SET ifVrf = '".$vrf_id."' WHERE interface_id = '".$interface['interface_id']."'");
          $if = $interface['interface_id'];
          $valid_vrf_if[$vrf_id][$if] = 1;
        }
        echo(") ");
      }
    }

    $sql = "SELECT * FROM ports WHERE device_id = '" . $device['device_id'] . "'";
    $data = mysql_query($sql);
    while ($row = mysql_fetch_assoc($data))
    {
      $if = $row['interface_id'];
      $vrf_id = $row['ifVrf'];
      if ($row['ifVrf'])
      {
        if (!$valid_vrf_if[$vrf_id][$if])
        {
          echo("-");
          $query = @mysql_query("UPDATE ports SET `ifVrf` = NULL WHERE interface_id = '$if'");
        }
        else
        {
          echo(".");
        }
      }
    }

    $sql = "SELECT * FROM vrfs WHERE device_id = '" . $device['device_id'] . "'";
    $data = mysql_query($sql);
    while ($row = mysql_fetch_assoc($data))
    {
      $vrf_id = $row['vrf_id'];
      if (!$valid_vrf[$vrf_id])
      {
        echo("-");
        $query = @mysql_query("DELETE FROM vrfs WHERE vrf_id = '$vrf_id'");
      }
      else
      {
        echo(".");
      }
    }

    unset($valid_vrf_if);
    unset($valid_vrf);

    echo("\n");
  } # cisco/junos/ironware
} # enable_vrfs

?>
