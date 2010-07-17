<?php

   echo("Microsoft Windows");

   if(strstr($sysDescr, "x86")) { $hardware = "Generic x86"; }
   if(strstr($sysDescr, "AMD64")) { $hardware = "Generic x64"; }

   if(strstr($sysDescr, "Build Number: 1381")) { $version = "NT 4.0"; }
   if(strstr($sysDescr, "Build 2195"))         { $version = "2000 (NT 5.0)"; }
   if(strstr($sysDescr, "Build 2600"))         { $version = "XP (NT 5.1)"; }
   if(strstr($sysDescr, "Build 3790"))         { $version = "XP / 2003 (NT 5.2)"; }
   if(strstr($sysDescr, "Build 6000"))         { $version = "Vista (NT 6.0)"; }
   if(strstr($sysDescr, "Build 6001"))         { $version = "Vista SP1 / 2008 (NT 6.0)"; }
   if(strstr($sysDescr, "Build 6001"))         { $version = "Vista SP2 / 2008 SP2 (NT 6.0)"; }
   if(strstr($sysDescr, "Build 6001"))         { $version = "7 / 2008 R2 (NT 6.1)"; }

   if(strstr($sysDescr, "Uniprocessor Free")) { $features = "Uniprocessor"; }
   if(strstr($sysDescr, "Multiprocessor Free")) { $features = "Multiprocessor"; }

   ### Detect processor type? : I.E.  x86 Family 15 Model 2 Stepping 7

include("ucd-mib.inc.php");
include("hr-mib.inc.php");

?>
