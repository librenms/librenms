<?php

if (!$os)
{
  if (strstr($sysObjectId, ".1.3.6.1.4.1.388")) { $os = "symbol"; }
  echo "symbol";    
}
else{
    echo "os already defined";
}

?>