<?php

// FIXME - wtfbbq

use App\Models\Device;
use Illuminate\Support\Facades\Gate;

if ($auth || Gate::allows('viewAll', Device::class)) {
    $id = $vars['id'];
    $title = generate_device_link($device);
    $auth = true;
}
