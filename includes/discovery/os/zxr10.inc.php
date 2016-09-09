<?php

if (!$os) {
    if (preg_match('/^ZXR10/', $sysDescr)) {
        $os = 'zxr10';
    }
    if (str_contains($sysDescr, "ZTE Ethernet Switch")) {
        $os = 'zxr10';
    }
}
