<?php

if (!$os)
{
  if (strstr($sysObjectId, ".1.3.6.1.4.1.12356.15")) { $os = "fortigate"; }
  if (strstr($sysObjectId, ".1.3.6.1.4.1.12356.101.1")) { $os = "fortigate"; }
}

?>