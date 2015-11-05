<?php
if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.9.6.1.82')) {
        $os = 'ciscosb';
    }

    if (strstr($sysObjectId, '.1.3.6.1.4.1.9.6.1.83')) {
        $os = 'ciscosb';
    }

    if (strstr($sysObjectId, '.1.3.6.1.4.1.9.6.1.85')) {
        $os = 'ciscosb';
    }

    if (strstr($sysObjectId, '.1.3.6.1.4.1.9.6.1.88')) {
        $os = 'ciscosb';
    }

    if (strstr($sysObjectId, '.1.3.6.1.4.1.9.6.1.89')) {
        $os = 'ciscosb';
    }
}
