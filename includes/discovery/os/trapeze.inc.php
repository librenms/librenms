<?php
//
// Discovery of Juniper Wireless (Trapeze) devices.
//
if (!$os) {
    if (preg_match('/^Juniper Networks/', $sysDescr)) {
        if (str_contains($sysDescr, 'MX-')) {
            $os = 'trapeze';
        }
        if (str_contains($sysDescr, 'WLC-')) {
            $os = 'trapeze';
        }
    }
}
