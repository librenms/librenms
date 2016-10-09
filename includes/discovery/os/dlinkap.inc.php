<?php

if (preg_match('/D-Link .* AP/', $sysDescr)) {
    $os = 'dlinkap';
} elseif (str_contains($sysDescr, array('D-Link DAP-', 'D-Link Access Point'))) {
    $os = 'dlinkap';
}
