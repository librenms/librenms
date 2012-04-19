<?php

if (!$os)
{
  if (strstr($sysDescr, "MGE Switched PDU")) { $os = "mgepdu"; }
}

?>