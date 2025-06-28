<?php

use LibreNMS\OS;

if (! isset($os) || ! $os instanceof OS) {
    $os = OS::make($device);
}

(new \LibreNMS\Modules\UcdMib())->discover($os);
