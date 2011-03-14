<?php

if (!$os)
{
  if (preg_match("/AXIS.*Network Camera/", $sysDescr)) { $os = "axiscam"; }
}

?>