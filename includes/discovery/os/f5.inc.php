<?php
if (!$os || $os === 'linux') {
    $f5_sys_parent = '1.3.6.1.4.1.3375.2.1';
    if (strpos($sysObjectId, $f5_sys_parent)) {
        $os = 'f5';
    }
}
