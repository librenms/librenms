<?php

if(!$os) {

  $sysObjectId = shell_exec($config['snmpget'] . " -Ovq -v2c -c ". $community ." ". $hostname ." .1.3.6.1.2.1.1.2.0");
  if(strstr($sysObjectId, "netscreen")) { $os = "ScreenOS"; } elseif (strstr($sysObjectId, "enterprises.3224.1")) { $os = "ScreenOS";  }


}

?>
