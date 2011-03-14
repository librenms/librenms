<?php

if (!$os)
{
  if (preg_match("/^NetBSD/", $sysDescr)) { $os = "netbsd"; }
}

?>