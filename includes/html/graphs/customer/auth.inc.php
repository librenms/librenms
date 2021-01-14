<?php

// FIXME - wtfbbq

if ($auth || Auth::user()->hasGlobalRead()) {
    $id = mres($vars['id']);
    $title = generate_device_link($device);
    $auth = true;
}
