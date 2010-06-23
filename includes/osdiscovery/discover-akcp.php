<?php

if (!$os) {

  if (preg_match("/8VD-X20/", $sysDescr)) { $os = "minkelsrms"; }
  if (preg_match("/SensorProbe/", $sysDescr)) { $os = "akcp"; }
  if (preg_match("/sensorProbe2/", $sysDescr)) { $os = "akcp"; }

}

?>
