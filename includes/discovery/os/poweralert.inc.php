<?php

if (!$os) {
    if (preg_match('/^POWERALERT/i', $sysDescr)) {
        $os = 'poweralert';
    }
}
