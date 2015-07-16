<?php

if (!$os) {
    if (preg_match('/^Supermicro Switch/', $sysDescr)) {
        $os = 'supermicro-switch';
    }
    if (preg_match('/^SSE-/', $sysDescr)) {
        $os = 'supermicro-switch';
    }
    if (preg_match('/^SBM-/', $sysDescr)) {
        $os = 'supermicro-switch';
    }
}
