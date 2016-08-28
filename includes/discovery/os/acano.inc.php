<?php

if (!$os) {
    if ((strstr($sysObjectId, '1.3.6.1.4.1.8072.3.2.10')) && (strstr($sysDescr, 'Acano'))) {
        $os = 'acano';
    }
}
