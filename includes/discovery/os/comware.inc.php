<?php

if (!$os) {
    if (strstr($sysDescr, 'Comware')) {
        $os = 'comware';
    }
    else if (preg_match('/HP [a-zA-Z0-9- ]+ Switch Software Version/', $sysDescr)) {
        $os = 'comware';
    }
}
