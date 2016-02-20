<?php
if (!$os) {
    // Canon Multifunction Printer/Scanner
    if (strstr($sysDescr, 'Canon MF') || strstr($sysDescr, 'Canon iR')) {
        $os = 'canonprinter';
    }
}
