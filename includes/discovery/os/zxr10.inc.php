<?php

if (starts_with('ZXR10', $sysDescr)) {
    $os = 'zxr10';
} elseif (str_contains('ZTE Ethernet Switch', $sysDescr)) {
    $os = 'zxr10';
}
