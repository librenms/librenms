<?php

if (str_contains($sysDescr, array('JETDIRECT', 'HP ETHERNET MULTI-ENVIRONMENT'))) {
    $os = 'jetdirect';
}

if (starts_with($sysObjectId, '.1.3.6.1.4.1.11.1')) {
    $os = 'jetdirect';
}
