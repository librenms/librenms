<?php

if (str_contains($sysDescr, array('ProCurve', 'HP 1820'))) {
    $os = 'procurve';
} elseif (preg_match('/eCos-[0-9.]+/', $sysDescr)) {
    $os = 'procurve';
} elseif (preg_match('/HP(.+)2530(.+)/', $sysDescr)) {
    $os = 'procurve';
} elseif (preg_match('/HP(.+)54[0-1][2-6]R(.+)/', $sysDescr)) {
    $os = 'procurve';
}
