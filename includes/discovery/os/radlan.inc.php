<?php

if (!$os) {
    if (strstr($sysDescr, 'AT-8000')) {
        $os = 'radlan';
    }           //end if
}
