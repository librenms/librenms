<?php

if (!$os)
{
  if (preg_match("/8VD-X20/", $sysDescr)) { $os = "minkelsrms"; }
  if (preg_match("/SensorProbe/i", $sysDescr)) { $os = "akcp"; }
}

?>