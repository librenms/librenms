<?php

$fabos_objectid = array(
    '.1.3.6.1.4.1.1588.2.1.1.1',
    '.1.3.6.1.4.1.1588.2.1.1.43',
    '.1.3.6.1.4.1.1588.2.1.1.72',
);

if (starts_with($sysObjectId, $fabos_objectid)) {
    $os = 'fabos';
}

unset($fabos_objectid);
