<?php

if (str_contains($sysDescr, array('Apple AirPort', 'Apple Base Station', 'Base Station V3.84'))) {
    $os = 'airport';
}
