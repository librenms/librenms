<?php

if (!$os) {
    if (strstr($sysDescr, 'Xerox Phaser')) {
        $os = 'xerox';
    }
    if (strstr($sysDescr, 'Xerox WorkCentre')) {
        $os = 'xerox';
    }
    if (stristr($sysDescr, 'FUJI XEROX DocuPrint')) {
        $os = 'xerox';
    }
}
