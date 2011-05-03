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


?>
