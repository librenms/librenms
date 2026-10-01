<?php

if ($auth || device_permitted($device['device_id'])) {
    $title = ' :: Custom OID ';
    $auth = true;
}
