<?php

if (!$os)
{
  if (preg_match("/^Vyatta/", $sysDescr)) { $os = "vyatta"; }
}

?>
