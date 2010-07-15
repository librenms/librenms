<?php

if(!$os) {

  if(strpos($sysDescr, "TG585v7") !== FALSE) { $os = "speedtouch"; }
  else if(strpos($sysDescr, "SpeedTouch 5") !== FALSE) { $os = "speedtouch"; }

}

?>
