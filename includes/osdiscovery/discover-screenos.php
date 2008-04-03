<?php

if(!$os) {

  $sysObjectId = shell_exec($config['snmpget'] . " -Ovq -m ".$config['installdir']."/mibs/NS-PRODUCTS.mib -v2c -c ". $community ." ". $hostname ." .1.3.6.1.2.1.1.2.0");
  if(strstr($sysObjectId, "netscreen")) { $os = "Netscreen"; }

}

?>
