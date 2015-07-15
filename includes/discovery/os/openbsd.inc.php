<?php

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.30155.23.1')) {
        $os = 'openbsd';
    } //end if
    if (preg_match('/OpenBSD/', $sysDescr)) {
        $os = 'openbsd';
    } //end if
}
