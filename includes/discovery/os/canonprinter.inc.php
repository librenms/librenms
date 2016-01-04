<?php
if (!$os) {
    // Canon Multifunction Printer/Scanner
    if (strstr($sysDescr, 'Canon MF')) {
        $os = 'canonprinter';
    }
}
