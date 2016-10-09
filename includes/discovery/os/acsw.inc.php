<?php

if (str_contains($sysDescr, array('Application Control Engine', 'Cisco Application Control Software'))) {
    $os = 'acsw';
} elseif (starts_with($sysObjectId, '.1.3.6.1.4.1.9.1.1291')) {
    $os = 'acsw';
}
