<?php

if (!$os)
{
  if (preg_match("/Redback/", $sysDescr)) { $os = "redback"; }
}

?>