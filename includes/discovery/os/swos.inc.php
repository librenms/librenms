<?php

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.14988.2')) {
        $os = 'swos';
    }
}
