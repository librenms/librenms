<?php

if (!$os)
{
  if (preg_match("/^Cisco\ Adaptive\ Security\ Appliance/", $sysDescr)) { $os = "asa"; }
}

?>