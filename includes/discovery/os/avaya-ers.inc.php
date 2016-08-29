<?php

if (!$os) {
    if (strstr($sysDescr, 'Ethernet Routing Switch')) {
        $os = 'avaya-ers';
    } elseif (strstr($sysDescr, 'ERS-')) {
        $os = 'avaya-ers';
    }
}
