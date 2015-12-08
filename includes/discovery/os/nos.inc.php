<?php

if (!$os) {
    if (strstr($sysDescr, "Brocade VDX") || strstr($sysObjectId, '.1.3.6.1.4.1.1588.2.2.1.1.1.5') || stristr($sysDescr, 'Brocade Communications Systems')) { 
        $os = "nos"; 
    }
}
