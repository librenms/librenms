<?php

if (!$os) {
    if (stristr($sysDescr, 'ProCurve') || stristr($sysDescr, 'HP 1820')) {
        $os = 'procurve';
    } elseif (preg_match('/eCos-[0-9.]+/', $sysDescr)) {
        $os = 'procurve';
    } elseif (preg_match('/HP(.+)2530(.+)/', $sysDescr)) {
        //hp aruba 2530 series
        $os = 'procurve';
    }
}
