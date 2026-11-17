<?php

use App\Models\Sensor;

if (! empty($pre_cache['ALCATEL-IND1-VIRTUAL-CHASSIS-MIB::virtualChassisVflMemberPortOperStatus'])
    && ! app('sensor-discovery')->canSkip(new Sensor(['sensor_class' => 'state']))) {
    $pre_cache['IF-MIB::ifName'] = SnmpQuery::cache()->walk('IF-MIB::ifName')->valuesByIndex();
}
