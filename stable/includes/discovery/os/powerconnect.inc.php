<?php

if (!$os)
{
  #if (strstr($sysDescr, "Neyland 24T")) { $os = "powerconnect"; } /* Powerconnect 5324 */
  if (stristr($sysDescr, "PowerConnect ")) { $os = "powerconnect"; }
  else if (preg_match("/Dell.*Gigabit\ Ethernet/i",$sysDescr)) { $os = "powerconnect"; }
}

?>