<?php

if (starts_with('1.3.6.1.4.1.311.1.1.3', $sysObjectId)) {
    $os = 'windows';
} elseif (str_contains('Windows', $sysDescr)) {
    $os = 'windows';
}
