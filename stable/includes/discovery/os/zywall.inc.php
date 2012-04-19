<?php

if (!$os)
{
  if (strpos($sysDescr, "ZyWALL") !== FALSE) { $os = "zywall"; }
}

?>