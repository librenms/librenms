<?php

if ($device['type'] == 'network' || $device['type'] == 'firewall' || $device['type'] == 'wireless') {
    if ($device['os'] == 'airos') {
        echo 'It Is Airos' . PHP_EOL;
        include 'includes/polling/mib/ubnt-airmax-mib.inc.php';
    } elseif ($device['os'] == 'airos-af') {
        echo 'It Is AirFIBER' . PHP_EOL;
        include 'includes/polling/mib/ubnt-airfiber-mib.inc.php';
    } elseif ($device['os'] == 'ceraos') {
        echo 'It is Ceragon CeroOS' . PHP_EOL;
        include 'includes/polling/mib/ceraos-mib.inc.php';
    } elseif ($device['os'] == 'siklu') {
        echo 'It is Siklu' . PHP_EOL;
        include 'includes/polling/mib/siklu-mib.inc.php';
    } elseif ($device['os'] == 'saf') {
        echo 'It is SAF Tehnika' . PHP_EOL;
        include 'includes/polling/mib/saf-mib.inc.php';
    } elseif ($device['os'] == 'sub10') {
        echo 'It is Sub10' . PHP_EOL;
        include 'includes/polling/mib/sub10-mib.inc.php';
    } elseif ($device['os'] == 'unifi') {
        include 'includes/polling/mib/ubnt-unifi-mib.inc.php';
    }
} else {
    echo 'Unsupported type: ' . $device['type'] . PHP_EOL;
}
