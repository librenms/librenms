<?php

/**
 * pmp.inc.php
 *
 * LibreNMS state polling module for Cambium PMP.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

use LibreNMS\OS\Pmp;

if ($sensor['sensor_type'] === 'cnMaestroConnectionStatus') {
    $sensor_value = Pmp::cnMaestroConnectionStatus($sensor_value);
}
