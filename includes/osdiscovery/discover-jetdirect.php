<?php

if(!$os) {

  if(strstr($sysDescr, "JETDIRECT")) { $os = "jetdirect"; }
  else if(strstr($sysDescr, "HP ETHERNET MULTI-ENVIRONMENT,")) { $os = "jetdirect"; }
}

?>
