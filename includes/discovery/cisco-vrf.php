<?

  unset( $vrf_count );

  echo("VRF : ");

  $oids = shell_exec($config['snmpwalk'] . " -CI -Ln -Osqn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " mplsVpnVrfRouteDistinguisher");

  $oids = str_replace(".1.3.6.1.3.118.1.2.2.1.3.", "", $oids);
  $oids = str_replace(" \"", "||", $oids);
  $oids = str_replace("\"", "", $oids);

  $oids = trim($oids);
  foreach ( explode("\n", $oids) as $oid ) {
   if($oid) {
    list($vrf['oid'], $vrf['MplsVpnVrfRouteDistinguisher']) = explode("||", $oid);
    $vrf['name'] = shell_exec($config['snmpget'] . " -Ln -Osq -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " mplsVpnVrfRouteDistinguisher.".$vrf['oid']);
    list(,$vrf['name'], $vrf['MplsVpnVrfRouteDistinguisher']) = explode("\"", $vrf['name']);
    echo($vrf['name']);

    if(@mysql_result(mysql_query("SELECT count(*) FROM vrfs WHERE `device_id` = '".$device['device_id']."'
                                 AND `vrf_oid`='".$vrf['oid']."'"),0)) {
      echo("already have! \n");
    } else {
      $insert_query  = "INSERT INTO `vrfs` (`vrf_oid`,`vrf_name`,`mplsVpnVrfRouteDistinguisher`,`device_id`) ";
      $insert_query .= "VALUES ('".$vrf['oid']."','".$vrf['name']."','".$vrf['MplsVpnVrfRouteDistinguisher']."','".$device['device_id']."')";
      mysql_query($insert_query);
    }
    $vrf_id = mysql_result(mysql_query("SELECT vrf_id FROM vrfs WHERE `device_id` = '".$device['device_id']."' AND `vrf_oid`='".$vrf['oid']."'"),0);
    echo(" Id: ($vrf_id) ");

    $interfaces_oid = ".1.3.6.1.3.118.1.2.1.1.2." . $vrf['oid'];
    $interfaces = shell_exec($config['snmpwalk'] . " -CI -Ln -Osqn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " $interfaces_oid");
    $interfaces = trim(str_replace($interfaces_oid . ".", "", $interfaces));
#    list($interfaces) = explode(" ", $interfaces);
    echo(" ( ");
    foreach (explode("\n", $interfaces) as $if_id) {
      $interface = mysql_fetch_array(mysql_query("SELECT * FROM interfaces WHERE ifIndex = '$if_id' AND device_id = '" . $device['device_id'] . "'"));
      echo($interface['ifDescr'] . " ");
      mysql_query("UPDATE interfaces SET ifVrf = '".$vrf_id."' WHERE interface_id = '".$interface['interface_id']."'");

    }
    echo(") ");

   }
  }


?>
