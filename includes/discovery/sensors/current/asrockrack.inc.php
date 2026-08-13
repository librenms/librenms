<?php

// ASRock Rack BMC current sensors, data collected in sensors/pre-cache/asrockrack.inc.php

foreach ($pre_cache['asrockrack']['current'] ?? [] as $asrock_sensor) {
    discover_sensor(
        null,
        'current',
        $device,
        $asrock_sensor['oid'],
        $asrock_sensor['index'],
        'asrockrack',
        $asrock_sensor['descr'],
        1,
        1,
        $asrock_sensor['low_limit'],
        $asrock_sensor['low_warn'],
        $asrock_sensor['warn'],
        $asrock_sensor['high'],
        $asrock_sensor['current']
    );
}
