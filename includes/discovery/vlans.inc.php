<?php

use LibreNMS\OS;

use App\Facades\LibrenmsConfig;

if (! $os instanceof OS) {
    $os = OS::make($device);
}
(new \LibreNMS\Modules\Vlans())->discover($os);
