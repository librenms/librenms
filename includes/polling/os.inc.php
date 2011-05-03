<?php

  if (is_file($config['install_dir'] . "/includes/polling/os/".$device['os'].".inc.php"))
  {
    /// OS Specific
    include($config['install_dir'] . "/includes/polling/os/".$device['os'].".inc.php");
  }
  elseif ($device['os_group'] && is_file($config['install_dir'] . "/includes/polling/os/".$device['os_group'].".inc.php"))
  {
    /// OS Group Specific
    include($config['install_dir'] . "/includes/polling/os/".$device['os_group'].".inc.php");
  }
  else
  {
    echo("Generic :(\n");
  }

  if ($version && $device['version'] != $version)
  {
    $device['db_update'] .= ", `version` = '".mres($version)."'";
    log_event("OS Version -> ".$version, $device, 'system');
  }

  if ($features != $device['features'])
  {
    $device['db_update'] .= ", `features` = '".mres($features)."'";
    log_event("OS Features -> ".$features, $device, 'system');
  }

  if ($hardware && $hardware != $device['hardware'])
  {
    $device['db_update'] .= ", `hardware` = '".mres($hardware)."'";
    log_event("Hardware -> ".$hardware, $device, 'system');
  }

  if ($serial && $serial != $device['serial'])
  {
    $device['db_update'] .= ", `serial` = '".mres($serial)."'";
    log_event("serial -> ".$serial, $device, 'system');
  }

  echo("Hardware: ".$hardware." Version: ".$version." Features: ".$features."\n");

?>
