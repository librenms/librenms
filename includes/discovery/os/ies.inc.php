<?php

if (!$os) {
    if (strstr($sysDescr, 'IES-') && !strstr($sysDescr, 'Cisco Systems')) {
        $os = 'ies';
    }
}
