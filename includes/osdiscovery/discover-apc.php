<?php

if(!$os) {

  if(strstr($sysDescr, "APC Web/SNMP Management Card")) { $os = "apc"; }
  else if(strstr($sysDescr, "APC Switched Rack PDU")) { $os = "apc"; }

}

?>
