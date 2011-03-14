<?php

if (!$os)
{
  if (strstr($sysDescr, "Arista Networks EOS")) { $os = "arista_eos"; }
}

?>