<?php
if (!$os) {
    if (strstr($sysDescr, 'Samsung CLX') ||
        strstr($sysDescr, 'Samsung SCX') ||
        strstr($sysDescr, 'Samsung C')) {
            $os = 'samsungprinter';
    }
}
