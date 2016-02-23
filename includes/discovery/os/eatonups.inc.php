<?php
if (!$os) {
    // Eaton UPS
    if (strstr($sysDescr, 'Eaton 5PX')) {
        $os = 'eatonups';
    }
}
