<?php

if (!$os) {
    if (preg_match('/D-Link .* AP/', $sysDescr)) {
        $os = 'dlinkap';
    }
    if (preg_match('/D-Link DAP-/', $sysDescr)) {
        $os = 'dlinkap';
    }
    if (preg_match('/D-Link Access Point/', $sysDescr)) {
        $os = 'dlinkap';
    }
}
