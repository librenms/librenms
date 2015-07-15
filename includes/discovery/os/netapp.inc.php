<?php

if (!$os) {
    if (stristr($sysDescr, "NetApp")) {
        $os = "netapp";
    }
}
