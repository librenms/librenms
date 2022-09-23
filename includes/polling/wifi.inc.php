<?php

// don't add new things here use wireless polling: https://docs.librenms.org/#Developing/os/Wireless-Sensors/

if ($device['os'] == 'airos-af') {
    echo 'It Is AirFIBER' . PHP_EOL;
    include 'includes/polling/mib/ubnt-airfiber-mib.inc.php';  // packet stats
} elseif ($device['os'] == 'siklu') {
    echo 'It is Siklu' . PHP_EOL;
    include 'includes/polling/mib/siklu-mib.inc.php';
} elseif ($device['os'] == 'sub10') {
    echo 'It is Sub10' . PHP_EOL;
    include 'includes/polling/mib/sub10-mib.inc.php';
}
