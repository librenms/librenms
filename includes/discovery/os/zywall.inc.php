<?php

if (!$os) {
    if (strstr($sysDescr, 'ZyWALL')) {
        $os = 'zywall';
    }
}
