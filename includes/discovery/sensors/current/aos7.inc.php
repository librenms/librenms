<?php

use App\Models\Sensor;
use LibreNMS\OS\Aos7;

/** @var Aos7 $os */
$ddm_table = 'ALCATEL-IND1-PORT-MIB::ddmPortInfoTable';
if (! empty($pre_cache[$ddm_table])
    && ! app('sensor-discovery')->canSkip(new Sensor(['sensor_class' => 'current']))) {
    $pre_cache[$ddm_table] = $os->ddmRowsWithPortNames($pre_cache[$ddm_table]);
}
