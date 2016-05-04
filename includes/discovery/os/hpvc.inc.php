<?php

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.11.5.7.5.1')) {
        $os = 'hpvc';
    }
}
