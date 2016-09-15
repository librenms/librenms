<?php
if (!$os) {
    // Sharp Multifunction Printer/Scanner
    if (strstr($sysDescr, 'SHARP MX-')) {
        $os = 'sharpprinter';
    }
}
