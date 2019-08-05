<?php

// FIXME - wtfbbq

if (Auth::user()->hasGlobalRead() || $auth) {
    $id    = mres($vars['id']);
    $title = generate_device_link($device);
    $auth  = true;
}
