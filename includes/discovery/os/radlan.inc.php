<?php

if (!$os) {
    if (strstr($sysDescr, 'Neyland 24T')) {
        $os = 'radlan';
    }       //end if

    if (strstr($sysDescr, 'AT-8000')) {
        $os = 'radlan';
    }           //end if
}
