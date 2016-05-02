<?php

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.19746.3.1')) {
        $os = 'datadomain';
    }
}
