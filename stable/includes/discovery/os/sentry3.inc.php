<?php

if (!$os)
{
  if (preg_match("/^Sentry\ Switched /", $sysDescr)) { $os = "sentry3"; }
}

?>