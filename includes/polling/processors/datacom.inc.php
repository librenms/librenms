<?php
   $proc = snmp_get($device, "swCpuUsage.0", "-Ovq", "DMswitch-MIB");
?>