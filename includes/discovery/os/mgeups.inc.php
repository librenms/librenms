<?php

if(!$os) {

  if(strstr($sysDescr, "Pulsar M")) { $os = "mgeups"; }
  else if(strstr($sysDescr, "Evolution S")) { $os = "mgeups"; }
  else if (preg_match("/^Galaxy /", $sysDescr)) { $os = "mgeups"; }

}

?>
