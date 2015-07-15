<?php

if (!$os) {
    if (strstr($sysDescr, 'NX-OS(tm)')) {
        $os = 'nxos';
    }
}
