<?php

if (!$os) {
    if (strstr($sysDescr, "Brocade VDX")||strstr($sysDescr, "BR-VDX")||strstr($sysDescr, "VDX67")) {
        $os = "nos"; 
    }
}
