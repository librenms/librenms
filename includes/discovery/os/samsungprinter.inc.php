<?php
if (!$os) {
    if (strstr($sysDescr, 'Samsung CLX')) {
        $os = 'samsungprinter';
    }
}
