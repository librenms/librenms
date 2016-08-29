<?php
if (!$os) {
    if (strstr($sysDescr, 'Samsung CLX') ||
        strstr($sysDescr, 'Samsung SCX') ||
        strstr($sysDescr, 'Samsun C')) {
            $os = 'samsungprinter';
    }
}
