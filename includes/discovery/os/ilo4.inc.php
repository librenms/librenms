<?php
if (!$os)
{
  if (preg_match("/^Integrated Lights-Out 4/", $sysDescr)) { $os = "ilo4"; }
}
?>
