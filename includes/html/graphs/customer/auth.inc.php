<?php

// FIXME - wtfbbq

if ($auth || Auth::user()->hasGlobalRead()) {
    $id = $vars['id'];
    $title = generate_device_link($device);
    $auth = true;
} elseif ($auth || Auth::user()->hasLimitedWrite()) {
    $id = $vars['id'];
    $title = generate_device_link($device);
    $auth = true;
}
