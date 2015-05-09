<?php

if (!$os)
{
  if (preg_match("/^Sentry\ (Switched|Smart) /", $sysDescr)) { $os = "sentry3"; }
}

?>
