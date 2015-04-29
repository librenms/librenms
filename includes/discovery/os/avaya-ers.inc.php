<?php

if (!$os)
{
  if (strstr($sysDescr, "Ethernet Routing Switch")) { $os = "avaya-ers"; }
  else if (strstr($sysDescr, "ERS-")) { $os = "avaya-ers"; }
}

?>
