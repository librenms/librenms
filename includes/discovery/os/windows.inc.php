<?php

if (str_contains('1.3.6.1.4.1.311.1.1.3', $sysObjectId) || str_contains('Windows', $sysDescr)) {
    $os = 'windows';
}
