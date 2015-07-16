<?php

if (!$os) {
    if (strstr($sysDescr, 'Dell Color Laser')) {
        $os = 'dell-laser';
    }
    if (strstr($sysDescr, 'Dell Laser Printer')) {
        $os = 'dell-laser';
    }
    if (preg_match('/^Dell.*MFP/', $sysDescr)) {
        $os = 'dell-laser';
    }
}
