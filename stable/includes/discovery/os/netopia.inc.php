<?php

if (!$os)
{
  if (preg_match("/^Netopia /", $sysDescr)) { $os = "netopia"; }
}

?>