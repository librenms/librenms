<?php

if (starts_with($sysObjectId, array('.1.3.6.1.4.1.4413', '.1.3.6.1.4.1.7244')) && str_contains($sysDescr, array('vxworks', 'quanta'),true)) {
    $os = 'quanta';
}
