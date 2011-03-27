<?php

if (!$os)
{
  if (strstr($sysObjectId, ".1.3.6.1.4.1.674.3224.1")) { $os = "screenos"; }
  if (strstr($sysObjectId, ".1.3.6.1.4.1.3224")) { $os = "screenos"; }
}

?>