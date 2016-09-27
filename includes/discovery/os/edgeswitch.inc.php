<?php

if (starts_with($sysObjectId, '.1.3.6.1.4.1.4413') && !str_contains($sysDescr, array('vxworks', 'Quanta', 'FASTPATH Switching'), true)) {
    $os = 'edgeswitch';
}
