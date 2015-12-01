<?php
if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.46242')) {
        $os = 'netonix';
    }
}
