<?php

if (!$os) {
    if (preg_match('/^Enterasys Networks/', $sysDescr)) {
        $os = 'enterasys';
    }
}
