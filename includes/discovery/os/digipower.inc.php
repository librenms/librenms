<?php

if (starts_with($sysDescr, 'Switched PDU')) {
    if (starts_with($sysObjectId, '.1.3.6.1.4.1.17420')) {
        $os = 'digipower';
    }
}
