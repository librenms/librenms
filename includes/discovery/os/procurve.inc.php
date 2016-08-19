<?php

if (!$os) {
    if (stristr($sysDescr, 'ProCurve')) {
        $os = 'procurve';
    }
    else if (preg_match('/eCos-[0-9.]+/', $sysDescr)) {
        $os = 'procurve';
    }
}
