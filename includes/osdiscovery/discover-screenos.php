<?php

if(!$os) {

  $sysObjectId = shell_exec($config['snmpget'] . " -Ovq -".$device['snmpver']." -c ". $device['community'] ." ". $device['hostname'].":".$device['port'] ." .1.3.6.1.2.1.1.2.0");
  if(strstr($sysObjectId, "netscreen")) { $os = "screenos"; } elseif (strstr($sysObjectId, "enterprises.3224.1")) { $os = "screenos";  }


}

?>
