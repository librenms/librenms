<?php

if (!$os)
{
  if (preg_match("/OpenBSD/", $sysDescr)) { $os = "openbsd"; }
}

?>
