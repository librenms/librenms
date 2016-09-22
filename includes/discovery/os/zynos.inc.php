<?php

if (starts_with('.1.3.6.1.4.1.890', $sysObjectId) && starts_with($sysDescr, array('ES', 'GS'))) {
    $os = 'zynos';
}
