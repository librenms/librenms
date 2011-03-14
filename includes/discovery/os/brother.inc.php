<?php

if (!$os)
{
  if (preg_match("/Brother NC-.*h,/", $sysDescr)) { $os = "brother"; }
}

?>