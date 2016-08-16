<?php

if (!$os) {
    if (strstr($sysDescr, 'Cisco Application Control Software')) {
        $os = 'acsw';
    }
    if (strstr($sysDescr, 'Application Control Engine')) {
        $os = 'acsw';
    }
}

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.9.1.1291')) {
        $os = 'acsw';
    }
}
