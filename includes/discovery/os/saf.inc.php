<?php

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.7571.100.1.1.5')) {
        $os = 'saf';
    }
}
