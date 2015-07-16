<?php

if (!$os) {
    if (preg_match('/^NetMan.*plus/', $sysDescr)) {
        $os = 'netmanplus';
    }

    if (strstr($sysObjectId, '.1.3.6.1.4.1.5491.6')) {
        $os = 'netmanplus';
    }
}
