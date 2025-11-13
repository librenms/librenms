<?php

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Str;

if ($device['os'] !== 'alteonos') {
    return;
}

if (! function_exists('alteon_mib_dirs')) {
    function alteon_mib_dirs(): array
    {
        return ['alteonos', 'radware', 'nortel'];
    }
}

if (! function_exists('alteon_mib_names')) {
    function alteon_mib_names(): array
    {
        static $mibs;

        if ($mibs !== null) {
            return $mibs;
        }

        $mibs = [];
        $installDir = rtrim(LibrenmsConfig::get('install_dir') ?: dirname(__DIR__, 4), '/');
        $mibDir = $installDir . '/mibs/alteonos';

        $addMib = static function (string $mib) use (&$mibs): void {
            if (! in_array($mib, $mibs, true)) {
                $mibs[] = $mib;
            }
        };

        foreach (['Radware', 'Nortel'] as $variant) {
            $mibFile = $mibDir . "/ALTEON-CHEETAH-LAYER4-$variant-MIB";
            if (is_file($mibFile)) {
                $addMib('+' . $mibFile);
            }
        }

        $addMib('layer4');

        return $mibs;
    }
}

if (! function_exists('alteon_snmp_get')) {
    function alteon_snmp_get($device, $oid, $options = '-OQv')
    {
        foreach (alteon_mib_dirs() as $mibDir) {
            foreach (alteon_mib_names() as $mibName) {
                $value = snmp_get($device, $oid, $options, $mibName, $mibDir);

                if ($value !== false && $value !== '' && ! Str::contains($value, 'No Such')) {
                    return $value;
                }
            }
        }

        return false;
    }
}

echo 'Alteon ';

$tempSensors = [
    [
        'oid' => 'hwTemperatureSensor1.0',
        'num_oid' => '.1.3.6.1.4.1.1872.2.5.1.3.1.22.0',
        'index' => '1',
        'descr' => 'Chassis Temperature Sensor 1',
    ],
    [
        'oid' => 'hwTemperatureSensor2.0',
        'num_oid' => '.1.3.6.1.4.1.1872.2.5.1.3.1.23.0',
        'index' => '2',
        'descr' => 'Chassis Temperature Sensor 2',
    ],
];

foreach ($tempSensors as $sensor) {
    $value = alteon_snmp_get($device, $sensor['oid']);

    if ($value === false || $value === '' || str_contains($value, 'No Such')) {
        continue;
    }

    if (! preg_match('/-?\d+(\.\d+)?/', $value, $matches)) {
        continue;
    }

    $current = (float) $matches[0];
    discover_sensor(null, 'temperature', $device, $sensor['num_oid'], 'alteonHwTemp.' . $sensor['index'], 'alteon-hw-temp', $sensor['descr'], 1, 1, null, null, null, null, $current);
}
