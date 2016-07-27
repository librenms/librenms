<?php

if (!$os) {
    if (strstr($sysDescr, "Brocade VDX")) {
        $os = "nos"; 
    }
    elseif (strstr($sysDescr, "BR-VDX")) {
        $os = "nos";
    }
}
