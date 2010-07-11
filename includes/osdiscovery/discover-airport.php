<?php

if(!$os) {

  if(strpos($sysDescr, "Apple AirPort") !== FALSE) { $os = "airport"; }
  else if(strpos($sysDescr, "Apple Base Station") !== FALSE) { $os = "airport"; }

}

?>
