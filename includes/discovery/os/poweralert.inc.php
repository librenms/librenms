<?php

if (!$os)
{
  if (preg_match("/^POWERALERT/", $sysDescr)) { $os = "poweralert"; }
}

?>
