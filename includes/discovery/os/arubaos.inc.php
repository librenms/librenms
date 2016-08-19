<?php

if (!$os) {
    if (strstr($sysDescr, 'ArubaOS')) {
        $os = 'arubaos';
    } elseif (preg_match('/HP(.+)2530(.+)/', $sysDescr)) {
        //hp aruba 2530 series
        $os = 'procurve';
    }
}
