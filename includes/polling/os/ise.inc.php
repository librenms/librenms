<?php

if ($device['sysObjectID'] == '.1.3.6.1.4.1.9.1.2139') {
    $hardware = 'SNS-3945';
} elseif ($device['sysObjectID'] == '.1.3.6.1.4.1.9.1.1426') {
    $hardware = 'Virtual Machine';
} else {
    $hardware = 'Unknown';
}
