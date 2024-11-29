<?php

use LibreNMS\Modules\EntityPhysical;
use LibreNMS\OS;

if (! isset($os) || ! $os instanceof OS) {
    $os = OS::make($device);
}
(new EntityPhysical())->discover($os);
