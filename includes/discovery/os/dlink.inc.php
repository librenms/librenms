<?php

if (!$os) {
    if (preg_match('/D-Link DES-/', $sysDescr)) {
        $os = 'dlink';
    }
    if (preg_match('/Dlink DES-/', $sysDescr)) {
        $os = 'dlink';
    }
    if (preg_match('/^D[CEG]S-/', $sysDescr)) {
        $os = 'dlink';
    }
}
