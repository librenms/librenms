<?php

if(!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.11863.1.1')) {
        $os = 'tplink';
    }
}
