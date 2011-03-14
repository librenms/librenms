<?php

if (!$os)
{
  if (preg_match("/D-Link DES-/", $sysDescr)) { $os = "dlink"; }
  else if (preg_match("/Dlink DES-/", $sysDescr)) { $os = "dlink"; }
  else if (preg_match("/^DES-/", $sysDescr)) { $os = "dlink"; }
  else if (preg_match("/^DGS-/", $sysDescr)) { $os = "dlink"; }
}

?>