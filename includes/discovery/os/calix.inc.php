<?php
if (!$os) {
    if (strstr($sysObjectId, '.6321.1.2.2.5.3')) {
        $os = 'calix';
    }
    if (strstr($sysObjectId, '.6066.1.44')) {
        $os = 'calix';
    }
    if (strstr($sysObjectId, '.6321.1.2.3')) { // E5-1xx
        $os = 'calix';
    }
}
