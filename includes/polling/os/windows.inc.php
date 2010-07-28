<?php

   echo("Microsoft Windows");

#sysDescr.0 = STRING: Hardware: x86 Family 6 Model 1 Stepping 9 AT/AT COMPATIBLE  - Software: Windows NT Version 4.0  (Build Number: 1381 Multiprocessor Free )
#sysDescr.0 = STRING: Hardware: x86 Family 6 Model 3 Stepping 4 AT/AT COMPATIBLE  - Software: Windows NT Version 3.51  (Build Number: 1057 Multiprocessor Free )
#sysDescr.0 = STRING: Hardware: x86 Family 16 Model 4 Stepping 2 AT/AT COMPATIBLE - Software: Windows 2000 Version 5.1 (Build 2600 Multiprocessor Free)
#sysDescr.0 = STRING: Hardware: x86 Family 15 Model 2 Stepping 5 AT/AT COMPATIBLE - Software: Windows 2000 Version 5.0 (Build 2195 Multiprocessor Free)
#sysDescr.0 = STRING: Hardware: AMD64 Family 16 Model 2 Stepping 3 AT/AT COMPATIBLE - Software: Windows Version 6.0 (Build 6002 Multiprocessor Free)
#sysDescr.0 = STRING: Hardware: EM64T Family 6 Model 26 Stepping 5 AT/AT COMPATIBLE - Software: Windows Version 5.2 (Build 3790 Multiprocessor Free)
#sysDescr.0 = STRING: Hardware: Intel64 Family 6 Model 23 Stepping 6 AT/AT COMPATIBLE - Software: Windows Version 6.1 (Build 7600 Multiprocessor Free)
#sysDescr.0 = STRING: Hardware: AMD64 Family 16 Model 8 Stepping 0 AT/AT COMPATIBLE - Software: Windows Version 6.1 (Build 7600 Multiprocessor Free)

   if(strstr($sysDescr, "x86"))     { $hardware = "Generic x86"; }
   if(strstr($sysDescr, "ia64"))    { $hardware = "Intel Itanium IA64"; }
   if(strstr($sysDescr, "EM64"))    { $hardware = "Intel x64"; }
   if(strstr($sysDescr, "AMD64"))   { $hardware = "AMD x64"; }
   if(strstr($sysDescr, "Intel64")) { $hardware = "Intel x64"; }  

   if(strstr($sysDescr, "Build Number: 1057")) { $version = "NT 3.51"; }
   if(strstr($sysDescr, "Build Number: 1381")) { $version = "NT 4.0"; }
   if(strstr($sysDescr, "Build 2195"))         { $version = "2000 (NT 5.0)"; }
   if(strstr($sysDescr, "Build 2600"))         { $version = "XP (NT 5.1)"; }
   if(strstr($sysDescr, "Build 3790"))         { $version = "XP / 2003 (NT 5.2)"; }
   if(strstr($sysDescr, "Build 6000"))         { $version = "Vista (NT 6.0)"; }
   if(strstr($sysDescr, "Build 6001"))         { $version = "Vista SP1 / 2008 (NT 6.0)"; }
   if(strstr($sysDescr, "Build 6002"))         { $version = "Vista SP2 / 2008 SP2 (NT 6.0)"; }
   if(strstr($sysDescr, "Build 7600"))         { $version = "7 / 2008 R2 (NT 6.1)"; }

   if(strstr($sysDescr, "Uniprocessor Free")) { $features = "Uniprocessor"; }
   if(strstr($sysDescr, "Multiprocessor Free")) { $features = "Multiprocessor"; }

   ### Detect processor type? : I.E.  x86 Family 15 Model 2 Stepping 7

?>
