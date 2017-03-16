<?php

if (!$os) {
    if (strstr($sysDescr, 'Hirschmann Railswitch')) {
        $os = 'hirschmann';
    }
}
