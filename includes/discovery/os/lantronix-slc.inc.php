<?php

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.244.1.1')) {
        $os = 'lantronix-slc';
    }
}
