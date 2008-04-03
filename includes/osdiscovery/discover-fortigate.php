<?php

if(!$os) {

  $sysObjectId = shell_exec($config['snmpget'] . " -Ovq -v2c -c ". $community ." ". $hostname ." .1.3.6.1.2.1.1.2.0");
  if(strstr($sysObjectId, "fortinet")) { 
    $fnSysVersion = shell_exec($config['snmpget'] . " -Ovq -v2c -c ". $community ." ". $hostname ." fnSysVersion.0");
    if(strstr($fnSysVersion, "Fortigate")) {
      $os = "Fortigate"; 
    }
  }

}

?>
