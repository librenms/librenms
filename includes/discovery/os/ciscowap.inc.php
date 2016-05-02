<?php

if (!$os) {
    if (strstr($sysDescr, 'Cisco Small Business WAP')) {
        $os = 'ciscowap';
    }
}
