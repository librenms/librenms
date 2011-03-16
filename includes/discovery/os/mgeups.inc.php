<?php

if (!$os)
{
  if (strstr($sysDescr, "Pulsar M")) { $os = "mgeups"; }
  else if (preg_match("/^Galaxy /", $sysDescr)) { $os = "mgeups"; }
  else if (preg_match("/^Evolution /", $sysDescr)) { $os = "mgeups"; }
  else if ($sysDescr == "MGE UPS SYSTEMS - Network Management Proxy") { $os = "mgeups"; }
}

?>