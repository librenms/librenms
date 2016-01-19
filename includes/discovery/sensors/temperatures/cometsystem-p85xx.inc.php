<?php

if ($device['os'] == 'cometsystem-p85xx') {
    $regexp = '/
        \.1\.3\.6\.1\.4\.1\.22626\.1\.5\.2\.
        (?P<id>\d+)
        \.
        (?:
                1\.0 (?P<name>.*)|
                3\.0 (?P<temp_intval>.*)|
                5\.0 (?P<limit_high>.*)|
                6\.0 (?P<limit_low>.*)|
        )
/x';

    $oids = snmp_walk($device, '.1.3.6.1.4.1.22626.1.5.2', '-OsqnU', '');
    if ($oids) {
        $out = array();
        foreach (explode("\n", $oids) as $line) {
            preg_match($regexp, $line, $match);
            if ($match['name']) {
                $out[$match['id']]['name'] = $match['name'];
            }

            if ($match['temp_intval']) {
                $out[$match['id']]['temp_intval'] = $match['temp_intval'];
            }

            if ($match['limit_high']) {
                $out[$match['id']]['limit_high'] = $match['limit_high'];
            }

            if ($match['limit_low']) {
                $out[$match['id']]['limit_low'] = $match['limit_low'];
            }
        }

        foreach ($out as $sensor_id => $sensor) {
            if ($sensor['temp_intval'] != 9999) {
                $temperature_oid = '.1.3.6.1.4.1.22626.1.5.2.'.$sensor_id.'.3.0';
                $temperature_id  = $sensor_id;
                $descr           = trim($sensor['name'], ' "');
                $lowlimit        = trim($sensor['limit_low'], ' "');
                $limit           = trim($sensor['limit_high'], ' "');
                $temperature     = $sensor['temp_intval'];

                discover_sensor($valid['sensor'], 'temperature', $device,
                    $temperature_oid, $temperature_id, 'cometsystem-p85xx',
                    $descr, '10', '1', $lowlimit, null, null, $limit, $temperature);
            }
        }
    }
}
