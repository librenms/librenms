<?php

  $hardware = trim(exec($config['snmpget'] . " -O vqs -m FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB -" . $device['snmpver'] . " -c " . $device['community'] . " " .
    $device['hostname'].":".$device['port'] . " sysObjectID.0"));

  $hardware = rewrite_ironware_hardware($hardware);

  $version = trim(exec($config['snmpget'] . " -O vqs -m FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB -" . $device['snmpver'] . " -c " . $device['community'] . " " .
    $device['hostname'].":".$device['port'] . " snAgBuildVer.0"));

  $version = str_replace("V", "", $version);
  $version = str_replace("\"", "", $version);

?>
