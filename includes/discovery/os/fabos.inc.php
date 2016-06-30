<?php

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.1588.2.1.1.1') || strstr($sysObjectId, '.1.3.6.1.4.1.1588.2.1.1.43')) {
        $os = 'fabos';
    }
}
