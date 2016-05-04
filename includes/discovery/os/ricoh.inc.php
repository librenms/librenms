<?php

if (!$os) {
    if (strstr($sysDescr, 'RICOH Aficio')) {
        $os = 'ricoh';
    }

    if (stristr($sysDescr, 'RICOH Network Printer')) {
        $os = 'ricoh';
    }
}
