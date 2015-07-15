<?php

if (!$os) {
    if (preg_match('/^CANOPY/', $sysDescr)) {
        $os = 'canopy';
    }
}
