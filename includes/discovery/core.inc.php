<?php

use LibreNMS\OS;
use LibreNMS\OS\Generic;

// start assuming no os
(new \LibreNMS\Modules\Core())->discover(Generic::make($device));

// then create with actual OS
$os = OS::make($device);
