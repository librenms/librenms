<?php

if (!$os) {
    if (strstr($sysDescr, 'IES-')) {
        $os = 'ies';
    }
}
