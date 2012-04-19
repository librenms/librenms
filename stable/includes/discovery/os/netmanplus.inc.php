<?php

if (!$os)
{
  if (preg_match("/^NetMan.*plus/", $sysDescr)) { $os = "netmanplus"; }
}

?>