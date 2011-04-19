<?php

if (!$os)
{
  if (strstr($sysObjectId, "1.3.6.1.4.1.311.1.1.3")) { $os = "windows"; }
  if (preg_match("/Windows/", $sysDescr)) { $os = "windows"; }
}

?>
