<?php

if (!$os)
{
  if (preg_match("/IronWare/", $sysDescr)) { $os = "ironware"; }
}

?>