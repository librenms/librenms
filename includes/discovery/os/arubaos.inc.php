<?php

if (!$os) {
    if (strstr($sysDescr, 'ArubaOS')) {
        $os = 'arubaos';
    }
}
