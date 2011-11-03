<?php

if (!$os)
{
  if (preg_match("/^Avocent/", $sysDescr)) { $os = "avocent"; }
  if (preg_match("/^AlterPath/", $sysDescr)) { $os = "avocent"; }
}

?>