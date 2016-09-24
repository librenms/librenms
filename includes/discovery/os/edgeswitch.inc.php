<?php

if (starts_with($sysObjectId, '.1.3.6.1.4.1.4413') && (!str_contains($sysDescr, 'vxworks') &&
    !str_contains($sysDescr, 'Quanta') && !str_contains($sysDescr, 'FASTPATH Switching'))) {
    $os = 'edgeswitch';
}
