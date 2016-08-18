<?php

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.5596.150.6.4.1')) {
        $os = 'vccodec';
    }
}
