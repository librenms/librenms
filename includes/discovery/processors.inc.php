<?php

use LibreNMS\OS;

$os ??= OS::make($device);
(new \LibreNMS\Modules\Processors())->discover($os);
