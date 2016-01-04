<?php
if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.4413') && (stristr($sysDescr, 'vxworks') || stristr($sysDescr, 'Quanta'))) {
        $os = 'quanta';
    }
}
