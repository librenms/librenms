<?php

if (!$os)
{
  if (stristr($sysDescr, "Blade Network Technologies")) { $os = "bnt"; }
  if (preg_match("/^BNT /", $sysDescr)) { $os = "bnt"; }
}

?>