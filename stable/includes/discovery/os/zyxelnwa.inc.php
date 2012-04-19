<?php

if (!$os)
{
  if (preg_match("/^NWA-/", $sysDescr)) { $os = "zyxelnwa"; }
}

?>