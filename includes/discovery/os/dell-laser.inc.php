<?php

if (str_contains($sysDescr, array('Dell Color Laser', 'Dell Laser Printer'))) {
        $os = 'dell-laser';
} elseif (preg_match('/^Dell.*MFP/', $sysDescr)) {
        $os = 'dell-laser';
}
