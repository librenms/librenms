<?php

use LibreNMS\OS;

if (! $os instanceof OS) {
    $os = OS::make($device);
}
(new \LibreNMS\Modules\OS())->poll($os);
