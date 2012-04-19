<?php

if (!$os)
{
  if (strstr($sysDescr, "NX-OS")) { $os = "nxos"; }
}

?>