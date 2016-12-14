<?php

if (!$os) {
    if (str_contains($sysDescr, 'SGOS')) {
        $os = 'sgos';
    }
}
