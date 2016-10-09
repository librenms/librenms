<?php
// Eaton UPS
if (starts_with($sysDescr, 'Eaton 5P') || starts_with($sysObjectId, '.1.3.6.1.4.1.534.')) {
    $os = 'eatonups';
}
