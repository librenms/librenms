<?php

if(!$os) {
  $fnSysVersion = shell_exec($config['snmpget'] . " -Ovq -".$device['snmpver']." -c ". $device['community'] ." ". $device['hostname'].":".$device['port'] ." 1.3.6.1.4.1.12356.1.3.0");
  if(strstr($fnSysVersion, "Fortigate")) {
    $os = "fortigate"; 
  }
}

?>
