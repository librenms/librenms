<?php

if (!$os) 
{
  if (strstr($sysDescr, "Neyland 24T")) { $os = "powerconnect"; }
  if (strstr($sysDescr, "PowerConnect ")) { $os = "powerconnect"; }
  if (preg_match("/Dell.*Gigabit\ Ethernet/i",$sysDescr)) { $os = "powerconnect"; } 
}

?>
