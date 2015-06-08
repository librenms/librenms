<?php

if (!$os) {
    if (preg_match("/^CS121/", $sysDescr)) {
	    if (strstr(snmp_get($device, "UPS-MIB::upsIdentManufacturer.0", "-Oqv", ""), "Multimatic")){ 
		    $os = "multimatic";
	    }
    }
}
