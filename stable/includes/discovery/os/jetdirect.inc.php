<?php

if (!$os)
{
  if (strstr($sysDescr, "JETDIRECT")) { $os = "jetdirect"; }
  else if (strstr($sysDescr, "HP ETHERNET MULTI-ENVIRONMENT")) { $os = "jetdirect"; }
  else if (strstr($sysObjectId, ".1.3.6.1.4.1.11.1")) { $os = "jetdirect"; }
}

?>