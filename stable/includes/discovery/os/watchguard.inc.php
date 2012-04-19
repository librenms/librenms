<?php

if (!$os)
{
  if (preg_match("/^WatchGuard\ Fireware/", $sysDescr)) { $os = "firebox"; }
}

?>
