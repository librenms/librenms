<?php
if (!$os || $os === 'linux') {
    if (stristr($sysObjectId, ".1.3.6.1.4.1.7779.1")) {
        $os = 'nios';
    }
}
