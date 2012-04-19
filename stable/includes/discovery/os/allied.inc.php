<?php

if (!$os)
{
  if (strstr($sysObjectId, ".1.3.6.1.4.1.207")) { $os = "allied"; }
  if (strstr($sysObjectId, ".1.3.6.1.4.1.207.1.4.126")) { unset($os);  }
}

?>