<?php
if (!$os) {
    if (strstr($sysDescr, 'SHARP MX-')) {
        $os = 'sharp';
    }
}
