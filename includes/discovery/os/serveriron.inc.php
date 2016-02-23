<?php

if (!$os) {
    if (preg_match('/ServerIron/', $sysDescr)) {
        $os = 'serveriron';
    }
}
