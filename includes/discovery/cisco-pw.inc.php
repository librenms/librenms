<?php

if ($config['enable_pseudowires'] && $device['os_group'] == "ios")
{
  unset($cpw_count);
  unset($cpw_exists);

  echo("Cisco Pseudowires : ");

  # FIXME snmp_walk
  $oids = shell_exec($config['snmpwalk'] . " -M " . $config['mibdir'] . " -m CISCO-IETF-PW-MIB -CI -Ln -Osqn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " cpwVcID");

  $oids = str_replace(".1.3.6.1.4.1.9.10.106.1.2.1.10.", "", $oids);

  $oids = trim($oids);
  foreach (explode("\n", $oids) as $oid)
  {
    if ($oid)
    {
      list($cpwOid, $cpwVcID) = explode(" ", $oid);

      if ($cpwOid)
      {
        list($cpw_remote_id) = explode(":", shell_exec($config['snmpget'] . " -M " . $config['mibdir'] . " -m CISCO-IETF-PW-MPLS-MIB -Ln -Osqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " cpwVcMplsPeerLdpID." . $cpwOid));
        $interface_descr = trim(shell_exec($config['snmpwalk'] . " -M " . $config['mibdir'] . " -m CISCO-IETF-PW-MIB -Oqvn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " cpwVcName." . $cpwOid));
        $cpw_remote_device = @mysql_result(mysql_query("SELECT device_id FROM ipv4_addresses AS A, ports AS I WHERE A.ipv4_address = '".$cpw_remote_id."' AND A.interface_id = I.interface_id"),0);
        $if_id = @mysql_result(mysql_query("SELECT `interface_id` FROM `ports` WHERE `ifDescr` = '$interface_descr' AND `device_id` = '".$device['device_id']."'"),0);

        if ($cpw_remote_device && $if_id)
        {
          $hostname = gethostbyid($cpw_remote_device);
#echo("\nOid: " . $cpwOid . " cpwVcID: " . $cpwVcID . " Remote Id: " . $cpw_remote_id . "($hostname(".$cpw_remote_device.") -> $interface_descr($if_id))");
          if (mysql_result(mysql_query("SELECT count(*) FROM pseudowires WHERE `interface_id` = '$if_id'
                  AND `cpwVcID`='".$cpwVcID."'"),0))
          {
            echo(".");
          }
          else
          {
            $insert_query  = "INSERT INTO `pseudowires` (`interface_id`,`peer_device_id`,`peer_ldp_id`,`cpwVcID`,`cpwOid`) ";
            $insert_query .= "VALUES ('$if_id','$cpw_remote_device','$cpw_remote_id','$cpwVcID', '$cpwOid')";
            mysql_query($insert_query);
            echo("+");
#echo($device['device_id'] . " $cpwOid $cpw_remote_device $if_id $cpwVcID\n");
          }
          $cpw_exists[] = $device['device_id'] . " $cpwOid $cpw_remote_device $if_id $cpwVcID";
        }
      }
    }
  }

  $sql = "SELECT * FROM pseudowires AS P, ports AS I, devices as D WHERE P.interface_id = I.interface_id AND I.device_id = D.device_id AND D.device_id = '".$device['device_id']."'";
  $query = mysql_query($sql);

  while ($cpw = mysql_fetch_assoc($query))
  {
    unset($exists);
    $i = 0;
    while ($i < count($cpw_exists) && !$exists)
    {
      $this_cpw = $cpw['device_id'] . " " . $cpw['cpwOid'] . " " . $cpw['peer_device_id'] . " " . $cpw['interface_id'] . " " . $cpw['cpwVcID'];
      if ($cpw_exists[$i] == $this_cpw) { $exists = 1;
#    echo($cpw_exists[$i]. " || $this_cpw \n");
      }
      $i++;
    }
    if (!$exists)
    {
      echo("-");
#    echo($this_cpw . "\n");
      mysql_query("DELETE FROM pseudowires WHERE pseudowire_id = '" . $cpw['pseudowire_id'] . "'");
    }
  }

  echo("\n");

} # enable_pseudowires + os_group=ios

?>