<?php
if ($device['os'] == "datacom") {
   echo("Datacom Switch : ");
   $descr = "CPU";
   $usage = snmp_get($device, "swCpuUsage.0", "-Ovq", "DMswitch-MIB");
   echo $usage."\n";
   if (is_numeric($usage)) {
      discover_processor($valid['processor'], $device, "swCpuUsage", "0", "datacom", $descr, "1", $usage, NULL, NULL);
   }
}
?>
