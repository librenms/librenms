<?php

if ($auth || device_permitted($device['device_id'])) {
    $title = generate_device_link($device);
    $title .= ' :: Custom OID ';
    $auth = true;
}
