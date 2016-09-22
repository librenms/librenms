<?php

if (starts_with($sysObjectId, '.1.3.6.1.4.1.890') && starts_with($sysDescr, array('ES', 'GS'))) {
    $os = 'zynos';
}
