<?php

if (!$os) {
    if (preg_match('/Cisco\ PIX/', $sysDescr)) {
        $os = 'pixos';
    }
}
