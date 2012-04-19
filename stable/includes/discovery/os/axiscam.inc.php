<?php

if (!$os)
{
  if (preg_match("/AXIS .* Network Camera/", $sysDescr)) { $os = "axiscam"; }
  if (preg_match("/AXIS .* Video Server/", $sysDescr)) { $os = "axiscam"; }
}

?>