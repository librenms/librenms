<?php

if (!$os) {
    if (stristr($sysDescr, "ProCurve")) {
        $os = "procurve";
    } elseif (preg_match("/eCos-[0-9.]+/", $sysDescr)) {
        $os = "procurve";
    }
}

?>
