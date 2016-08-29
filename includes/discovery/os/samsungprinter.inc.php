<?php
if (!$os) {
    if (strstr($sysDescr, 'Samsung CLX') ||
        strstr($sysDescr, 'Samsung SCX') ||
        strstr($sysDescr, 'Samsung C') ||
        strstr($sysDescr, 'Samsung S')) {
            $os = 'samsungprinter';
    }
}
