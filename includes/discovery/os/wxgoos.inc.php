<?php

if (str_contains($sysDescr, 'NETOS 6.0')) {
    $os = 'wxgoos';
}

if (starts_with($sysObjectId, array('.1.3.6.1.4.1.901.1', '.1.3.6.1.4.1.17373'))) {
    $os = 'wxgoos';
}
