<?php

if (starts_with($sysDescr, 'ZyWALL 2X')) {
    $os = 'zywall';
}

if (starts_with($sysObjectId, array('.1.3.6.1.4.1.890.1.6', '.1.3.6.1.4.1.890.1.15'))) {
    $os = 'zywall';
}
