<?php
if (!$os) {
        if (strstr($sysObjectId, '.1.3.6.1.4.1.890.1.5.8.62') || strstr($sysObjectId, '.1.3.6.1.4.1.890.1.5.8.63'))
                $os = "zyxeles";
}
