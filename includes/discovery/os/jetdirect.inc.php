<?php

if (str_contains($sysDescr, array('JETDIRECT', 'HP ETHERNET MULTI-ENVIRONMENT'))) {
    $os = 'jetdirect';
} elseif (starts_with($sysObjectId, '.1.3.6.1.4.1.11.1') && !starts_with($sysObjectId, '.1.3.6.1.4.1.11.10.2.1.3.25')) {
    $os = 'jetdirect';
}
