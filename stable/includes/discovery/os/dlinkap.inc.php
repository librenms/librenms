<?php

if (!$os)
{
  if (preg_match("/D-Link .* AP/", $sysDescr)) { $os = "dlinkap"; }
  else if (preg_match("/D-Link DAP-/", $sysDescr)) { $os = "dlinkap"; }
  else if (preg_match("/D-Link Access Point/", $sysDescr)) { $os = "dlinkap"; }
}

?>