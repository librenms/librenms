<?php
if (!$os) {
    // Eaton UPS
    if (str_contains($sysDescr, 'Eaton 5P')) {
        $os = 'eatonups';
    }
}
