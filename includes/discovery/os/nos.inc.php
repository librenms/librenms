<?php

$nos_objectid = array(
    '.1.3.6.1.4.1.1588.2.2.1.1',
);

if (str_contains($sysDescr, array('Brocade VDX', 'BR-VDX', 'VDX67'))) {
    $os = 'nos';
} elseif (starts_with($sysObjectId, $nos_objectid)) {
    $os = 'nos';
}

unset($nos_objectid);
