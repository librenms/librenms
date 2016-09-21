<?php

if (str_contains('.1.3.6.1.4.1.890', $sysObjectId) && (starts_with($sysDescr, array('EG', 'GS')))) {
    $os = 'zynos';
}
