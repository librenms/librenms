<?php

if (!$os)
{
  if (strstr($sysDescr, "SAN-OS")) { $os = "sanos"; }
}

?>