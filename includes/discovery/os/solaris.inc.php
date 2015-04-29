<?php

if (!$os)
{
  if (preg_match("/^SunOS/", $sysDescr))
  {
    $os = "solaris";
    list(,,$version) = explode (" ", $sysDescr);
    if ($version > "5.10") { $os = "opensolaris"; }
    if ($version > "5.10") {
      if (preg_match("/oi_/", $sysDescr)) { $os = "openindiana"; }
    }
  }

  if (strstr($sysObjectId, ".1.3.6.1.4.1.42.2.1.1")) { $os = "solaris"; }
}

?>