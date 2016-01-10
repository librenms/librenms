<?php

if (!$os) {
    if (preg_match('/^Enterasys Networks/', $sysDescr)) {
        $os = 'enterasys';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.5624.2.1')) {
        $os = 'enterasys';
    }
}