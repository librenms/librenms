<?php

if ($device['os'] == "nos") {
<<<<<<< HEAD
  echo("nos: ");

  $used 	= snmp_get($device, "1.3.6.1.4.1.1588.2.1.1.1.26.6.0", "-Ovq");
  $total 	= "100";
  $free		= ($total - $used);

  $percent	= $used; 

  if (is_numeric($used)) {
    discover_mempool($valid_mempool, $device, 0, "nos", "Memory", "1", NULL, NULL);
  }
}
=======
    echo("nos: ");

    $used 	= snmp_get($device, "1.3.6.1.4.1.1588.2.1.1.1.26.6.0", "-Ovq");
    $total 	= "100";
    $free		= ($total - $used);

    $percent	= $used; 

    if (is_numeric($used)) {
        discover_mempool($valid_mempool, $device, 0, "nos", "Memory", "1", NULL, NULL);
    }
}
>>>>>>> e380cc5100753e69e9e57dd6a2e3f93be3cdcdf2
