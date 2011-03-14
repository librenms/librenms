<?php

if (!$os)
{
  if (strstr($sysDescr, "Raid Subsystem V")) { $os = "areca"; }
}

?>