<?php

if (!$os)
{
  if ($sysDescr == 'NETOS 6.0')
  {
    if (strstr($sysObjectId, ".1.3.6.1.4.1.901.1")) { $os = "wxgoos"; }
  }
  if (strstr($sysObjectId, ".1.3.6.1.4.1.17373")) { $os = "wxgoos"; }
}

?>