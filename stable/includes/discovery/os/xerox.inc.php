<?php

if (!$os)
{
  if (strstr($sysDescr, "Xerox Phaser")) { $os = "xerox"; }
  else if (strstr($sysDescr, "Xerox WorkCentre")) { $os = "xerox"; }
}

?>