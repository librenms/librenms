<?php

  unset( $cpw_count );

  echo("PW : ");

  $oids = shell_exec($config['snmpwalk'] . " -CI -Ln -Osqn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " cpwVcID");

  $oids = str_replace(".1.3.6.1.4.1.9.10.106.1.2.1.10.", "", $oids);

  $oids = trim($oids);
  foreach ( explode("\n", $oids) as $oid ) {
   if($oid) {
    list($cpwOid, $cpwVcID) = explode(" ", $oid);
    if($cpwOid) {
      list($cpw_remote_id) = split(":", shell_exec($config['snmpget'] . " -Ln -Osqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " cpwVcMplsPeerLdpID." . $cpwOid));
      $interface_descr = trim(shell_exec("snmpwalk -Oqvn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " cpwVcName." . $cpwOid));
      $cpw_remote_device = @mysql_result(mysql_query("SELECT device_id FROM ipaddr AS A, interfaces AS I WHERE A.addr = '".$cpw_remote_id."' AND A.interface_id = I.interface_id"),0);
      $if_id = @mysql_result(mysql_query("SELECT `interface_id` FROM `interfaces` WHERE `ifDescr` = '$interface_descr' AND `device_id` = '".$device['device_id']."'"),0);
      if($cpw_remote_device && $if_id) {
        $hostname = gethostbyid($cpw_remote_device);
        echo("Oid: " . $cpwOid . " cpwVcID: " . $cpwVcID . " Remote Id: " . $cpw_remote_id . "($hostname(".$cpw_remote_device.") -> $interface_descr($if_id)) \n");

        if(mysql_result(mysql_query("SELECT count(*) FROM pseudowires WHERE `interface_id` = '$if_id'
                                     AND `cpwVcID`='".$cpwVcID."'"),0)) {
  	  echo("already have! \n");
        } else {
          $insert_query  = "INSERT INTO `pseudowires` (`interface_id`,`peer_device_id`,`peer_ldp_id`,`cpwVcID`,`cpwOid`) ";
          $insert_query .= "VALUES ('$if_id','$cpw_remote_device','$cpw_remote_id','$cpwVcID', '$cpwOid')"; 
          mysql_query($insert_query);
        }

      }
    }
  }
}

?>
