<?php

if (starts_with($sysDescr, 'Power Manager')) {
    if (starts_with($sysObjectId, '.1.3.6.1.4.1.3808.1.1.3')) {
        $os = 'cyberpower';
    }
}
