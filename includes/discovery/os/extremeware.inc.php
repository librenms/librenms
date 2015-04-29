<?php

if (!$os)
{
  if (strstr($sysObjectId, ".1.3.6.1.4.1.1916.2")) { $os = "extremeware"; }
  if (strstr($sysDescr, "XOS")) { $os = "xos"; }
}

?>