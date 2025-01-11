<?php

use Illuminate\Support\Facades\Auth;

if ($auth || Auth::user()->canAccessDevice($device['device_id'])) {
    $title = generate_device_link($device);
    $title .= ' :: Custom OID ';
    $auth = true;
}
