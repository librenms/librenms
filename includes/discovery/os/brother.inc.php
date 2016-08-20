<?php

if (!$os) {
    if (preg_match('/Brother NC-.*(h|w),/', $sysDescr)) {
        $os = 'brother';
    }
}
