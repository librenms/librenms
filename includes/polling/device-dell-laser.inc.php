<?php

  $hardware = trim(exec($config['snmpget'] . " -O Qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .
    $device['hostname'].":".$device['port'] . " hrDeviceDescr.1"));
  list(,$version) = split('Engine ',$sysDescr);
  
  $version = "Engine " . trim($version,')');
?>
