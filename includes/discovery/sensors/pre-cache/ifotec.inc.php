<?php
/**
 * ifotec.inc.php
 *
 * Grab all data under IFOTEC enterprise oid and process it for yaml consumption
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  LibreNMS contributors
 * @author     Cedric MARMONIER
 */
if (Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.21362.100.')) {
    $pre_cache['ifoSysProductIndex'] = snmp_get($device, 'ifoSysProductIndex.0', '-Oqv', 'IFOTEC-SMI');

    if ($pre_cache['ifoSysProductIndex'] != null) {
        $virtual_tables = [
            'ifoTempName'            => '/\.1\.3\.6\.1\.4\.1\.21362\.101\.2\.1\.1\.3\.' . $pre_cache['ifoSysProductIndex'] . '\.(\d+)/',
            'ifoTempDescr'           => '/\.1\.3\.6\.1\.4\.1\.21362\.101\.2\.1\.1\.4\.' . $pre_cache['ifoSysProductIndex'] . '\.(\d+)/',
            'ifoTempValue'           => '/\.1\.3\.6\.1\.4\.1\.21362\.101\.2\.1\.1\.5\.' . $pre_cache['ifoSysProductIndex'] . '\.(\d+)/',
            'ifoTempAlarmStatus'     => '/\.1\.3\.6\.1\.4\.1\.21362\.101\.2\.1\.1\.6\.' . $pre_cache['ifoSysProductIndex'] . '\.(\d+)/',
            'ifoTempLowThldAlarm'    => '/\.1\.3\.6\.1\.4\.1\.21362\.101\.2\.1\.1\.7\.' . $pre_cache['ifoSysProductIndex'] . '\.(\d+)/',
            'ifoTempHighThldAlarm'   => '/\.1\.3\.6\.1\.4\.1\.21362\.101\.2\.1\.1\.8\.' . $pre_cache['ifoSysProductIndex'] . '\.(\d+)/',
            'ifoTempLowThldWarning'  => '/\.1\.3\.6\.1\.4\.1\.21362\.101\.2\.1\.1\.9\.' . $pre_cache['ifoSysProductIndex'] . '\.(\d+)/',
            'ifoTempHighThldWarning' => '/\.1\.3\.6\.1\.4\.1\.21362\.101\.2\.1\.1\.10\.' . $pre_cache['ifoSysProductIndex'] . '\.(\d+)/',
        ];

        // .ifoTemperatureTable.ifoTemperatureEntry.<ifoSysProductIndex>
        $data = snmp_walk($device, 'ifoTemperatureEntry', '-OQn', 'IFOTEC-SMI');
        var_dump($data);
        foreach (explode(PHP_EOL, $data) as $line) {
            [$oid, $value] = explode(' = ', $line);

            $processed = false;
            foreach ($virtual_tables as $vt_name => $vt_regex) {
                if (preg_match($vt_regex, $oid, $matches)) {
                    $index = $matches[1];

                    $pre_cache['ifoTemperatureTable'][$index][$vt_name] = ['value' => $value, 'oid' => $oid];

                    $processed = true;
                    break;  // skip rest
                }
            }

            if (! $processed) {
                $pre_cache[$oid] = [[$oid => $value]];
            }
        }
        var_dump($pre_cache['ifoTemperatureTable']);
    }
}
unset($data);
