<?php

if(!$os) {
  $fnSysVersion = snmp_get($device, "1.3.6.1.4.1.12356.1.3.0", "-Ovq");
  if(strstr($fnSysVersion, "Fortigate")) {
    $os = "fortigate"; 
  }
}

?>
