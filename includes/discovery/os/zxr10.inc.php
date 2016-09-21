<?php

if (starts_with('ZXR10', $sysDescr) || str_contains('ZTE Ethernet Switch', $sysDescr)) {
    $os = 'zxr10';
}
