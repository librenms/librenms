<?php

if (Auth::user()->hasGlobalRead()) {
    echo view('service.create', ['device_id' => $device['device_id']]);
} else {
    include 'includes/html/error-no-perm.inc.php';
}
