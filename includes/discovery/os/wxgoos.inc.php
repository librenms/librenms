<?php

if (str_contains('NETOS 6.0', $sysDescr) && starts_with($sysObjectId, array('.1.3.6.1.4.1.901.1', '.1.3.6.1.4.1.17373'))) {
    $os = 'wxgoos';
}
