<?php
if (!$os) {
    // Eaton UPS
    if (strstr($sysDescr, 'Eaton 5PX')) {
        $os = 'eatonups';
    }
	    if (strstr($sysDescr, 'Eaton 5P')) {
        $os = 'eatonups';
    }
	    if (strstr($sysDescr, 'Eaton 9PX')) {
        $os = 'eatonups';
    }
	    if (strstr($sysDescr, 'Eaton 9130')) {
        $os = 'eatonups';
    }
		    if (strstr($sysDescr, 'Eaton Evolution')) {
        $os = 'eatonups';
    }
		    if (strstr($sysDescr, 'Eaton EX')) {
        $os = 'eatonups';
    }
		    if (strstr($sysDescr, 'Eaton 93E')) {
        $os = 'eatonups';
    }
}
