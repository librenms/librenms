<?php
if (!$os) {
    if (strstr($sysDescr, 'Lexmark ')) {
        $os = 'lexmarkprinter';
    }
}
