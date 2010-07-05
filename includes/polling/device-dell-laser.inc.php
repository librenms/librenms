<?php

  $hardware = snmp_get($device, "hrDeviceDescr", "-OQv", "HOST-RESOURCES-MIB");

  list(,$version) = split('Engine ',$sysDescr);
  
  $version = "Engine " . trim($version,')');

  if (strstr($hardware  ,';'))
  {
    $hardware = substr($hardware,0,strpos($hardware,';'));
  }

?>
