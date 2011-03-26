<?php

if (!$os)
{
  if (preg_match("/^VMware ESX/", $sysDescr)) { $os = "vmware"; }
}

?>