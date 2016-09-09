<?php
if (!$os) {
    if (str_contains($sysObjectId, ".1.3.6.1.4.1.7779.1")) {
        $os = 'nios';
    }
}
