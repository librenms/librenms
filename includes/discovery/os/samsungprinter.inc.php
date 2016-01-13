<?php
if (!$os) {
    if (strstr($sysDescr, 'Samsung CLX') || strstr($sysDescr, 'Samsung SCX')) {
        $os = 'samsungprinter';
    }
}
