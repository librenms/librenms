<?php

if (!$os)
{
  if (preg_match("/^Net Vision/", $sysDescr)) { $os = "netvision"; }
}

?>
