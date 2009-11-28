<?php

if(!$os) {

  $sysObjectId = shell_exec($config['snmpget'] . " -Ovqn -".$device['snmpver']." -c ". $device['community'] ." ". $device['hostname'].":".$device['port'] ." sysObjectID.0");
  if(strstr($sysObjectId, ".1.3.6.1.4.1.2636")) { $os = "junos"; }

}

?>
