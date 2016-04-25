<?php

if (!$os) {
    if (strstr($sysDescr, 'Viprinet VPN Router')) {
        $os = 'viprinux';
    }
}
