<?php

if(!$os) {

  if(strstr($sysObjectId, "netscreen")) { $os = "screenos"; } elseif (strstr($sysObjectId, ".1.3.6.1.4.1.674.3224.1")) { $os = "screenos";  }


}

?>
