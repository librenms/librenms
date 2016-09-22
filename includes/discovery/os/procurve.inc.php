<?php

if (str_contains($sysDescr, array('ProCurve', 'HP 1820'))) {
    $os = 'procurve';
}

if (str_contains($sysDescr, 'HP') && str_contains($sysDescr, array('2530', '54'))) {
    $os = 'procurve';
}

if (str_contains($sysDescr, 'eCos-')) {
    $os = 'procurve';
}
