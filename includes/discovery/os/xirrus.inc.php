<?php

if(!$os) {
    if (strstr($sysDescr, 'Xirrus')) {
	if (strstr($sysDescr, 'ArrayOS')) {
	        $os = 'xirrus_aos';
	}
    }
}

