<?php

if ($device['sysObjectID'] == 'enterprises.9.1.2139') {
    $hardware = 'SNS-3945';
} elseif ($device['sysObjectID'] == 'enterprises.9.1.1426') {
    $hardware = 'Virtual Machine';
} else {
    $hardware = 'Unknown';
}
