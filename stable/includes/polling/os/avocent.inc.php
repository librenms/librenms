<?php

if (strstr($poll_device['sysDescr'], "AlterPath"))
{
  list($hardware, $version) = explode("-", trim(str_replace("version:", "", (str_replace("V_", "", $poll_device['sysDescr'])))), 2);
  $hardware = trim($hardware);
  $version  = trim($version);
  $features = trim(str_replace("#1", "", $version));
}

?>
