<?php

   if(strstr($ciscomodel, "OID")){ unset($ciscomodel); }
   if(!strstr($ciscomodel, " ") && strlen($ciscomodel) >= '3') {
     $hardware = $ciscomodel;
   }

   $sysDescr = str_replace("IOS (tm)", "IOS (tm),", $sysDescr);
   list(,$features,$version) = explode(",", $sysDescr);
   $version = str_replace("Copyright", "", $version);
   list(,$version,) = explode(" ", trim($version));
   list(,$features) = explode("(", $features);
   list(,$features) = explode("-", $features);


?>
