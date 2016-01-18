<?php

if (!$os) {
    if (strstr($sysDescr, "Brocade VDX")) {
        $os = "nos"; 
    }
}
