<?php
/**
 * web600.inc.php
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
 *
 * @copyright  2021 Beanfield Technologies Inc
 * @author     Jeremy Ouellet <jouellet@beanfield.com>
 */
$oidmap = [
'ioCalib' => '2',
'ioDatalogNormInt' => '4',
'ioDatalogExInt' => '5',
'ioAlarmLow' => '11',
'ioAlarmHigh' => '12',
'ioTblLow' => '13',
'ioTblHigh' => '14',
'ioName' => '15',
'ioRecTime' => '16',
'ioResetTime' => '17',
'ioType' => '18',
'ioUnits' => '19',
'ioUnitsType' => '20',
'ioPointEnb' => '21',
'ioAlarmEnb' => '22',
'ioAlarmOnClear' => '25',
'ioIndexID' => '31',
'ioGaugeLow' => '33',
'ioGaugeHigh' => '34',
'ioMin' => '40',
'ioMax' => '41',
'ioEntry.42' => '42',
'ioEntry.43' => '43',
'ioLastAlarm' => '45',
'ioValue' => '48',
'ioValueStr' => '49',
'ioStatus' => '50',
'ioRange' => '51',
'ioUnack' => '52',
'ioValueInt' => '53',
'ioMinInt' => '54',
'ioMaxInt' => '55',
'ioEntry.56' => '56',
'ioEntry.60' => '60',
'ioResetMin' => '71',
'ioResetMax' => '72',
'ioSetDefaults' => '73',
'ioMinStr' => '74',
'ioMaxStr' => '75',
'ioEntry.76' => '76',
'ioEntry.77' => '77',
'ioEntry.79' => '79',
'ioSchedule' => '90',
'ioStatusStr' => '91',
'ioTypeStr' => '92',
'ioCategory' => '95',
'ioADcalib' => '96',
'io420calib' => '97',
'ioLastAlarmStr' => '98',
'ioCalibFloat' => '101',
'ioEntry.104' => '104',
];

echo 'ioTable ';
$ids = snmpwalk_group($device, 'hostIO', 'SENSAPHONE-MIB');
$iotable = [];
foreach ($ids as $key => $value) {
    if (strpos($key, 'ioTable') !== false) {
        $item = explode('.', $key);
        $iotable[$item[1]][$item[2]] = $value;
    } else {
        $iotable[1][$oidmap[$key]] = $value;
    }
}
$pre_cache['web600-ioTable'] = $iotable;
