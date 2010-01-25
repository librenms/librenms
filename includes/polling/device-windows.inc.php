<?php

   if(strstr($sysDescr, "x86")) { $hardware = "Generic x86"; }
   if(strstr($sysDescr, "AMD64")) { $hardware = "Generic x64"; }
   if(strstr($sysDescr, "Windows 2000 Version 5.1")) { $version = "XP"; }
   if(strstr($sysDescr, "Windows Version 5.2")) { $version = "2003 Server"; }
   if(strstr($sysDescr, "Windows Version 6.1")) { $version = "Windows 7"; }
   if(strstr($sysDescr, "Uniprocessor Free")) { $features = "Uniprocessor"; }
   if(strstr($sysDescr, "Multiprocessor Free")) { $features = "Multiprocessor"; }

include("ucd-mib.inc.php");
include("hr-mib.inc.php");

?>
