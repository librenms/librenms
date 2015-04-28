<?php

if (!$os)
{
  if ($sysDescr == "SNMP TME") { $os = "papouch-tme"; }
  else if ($sysDescr == "TME") { $os = "papouch-tme"; }
}

?>