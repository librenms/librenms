<?php

if (!$os)
{
  if (preg_match("/^AXIS .* Network Document Server/", $sysDescr)) { $os = "axisdocserver"; }
}

?>