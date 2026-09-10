<?php

use LibreNMS\OS;

if (! $os instanceof OS) {
    $os = OS::make($device);
}
(new \LibreNMS\Modules\Ospfv3())->poll($os, app('Datastore'));
