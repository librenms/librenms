<?php

// FIXME - wtfbbq
use LibreNMS\Authentication\LegacyAuth;

if (LegacyAuth::user()->hasGlobalRead() || $auth) {
    $id    = mres($vars['id']);
    $title = generate_device_link($device);
    $auth  = true;
}
