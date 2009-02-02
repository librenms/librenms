<?php

if(!$os) {

  $sysObjectId = shell_exec($config['snmpget'] . " -Ovqn -v2c -c ". $community ." ". $hostname.":".$port ." sysObjectID.0");
  if(strstr($sysObjectId, ".1.3.6.1.4.1.2636")) { $os = "JunOS"; }

}

?>
