<?php

if (!$os)
{
  if (preg_match("/^ES-/", $sysDescr)) { $os = "zyxeles"; }
}

?>