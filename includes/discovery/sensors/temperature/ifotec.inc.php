<?php
/**
 * ifotec.inc.php
 *
 * Ifotec temperature sensors
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  LibreNMS contributors
 * @author     Cedric MARMONIER
 */
// To see : https://docs.librenms.org/Developing/os/Health-Information/

// *************************************************************
//echo "#### Call discovery.temperature ifotec.inc.php #########################################################\n";

$index = 0;
foreach ($pre_cache['ifoTemperatureTable'] as $ifoSensor) {
    //echo "  create ifotec sensor : " . $ifoSensor['ifoTempDescr']['value'] . "\n";

    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,        
        $ifoSensor['ifoTempValue']['oid'],
        $ifoSensor['ifoTempName']['value'], // each sensor id must be unique
        'ifotecSensor',
        $ifoSensor['ifoTempDescr']['value'],
        10, // divider
        1, // multiplier
        $ifoSensor['ifoTempLowThldAlarm']['value'] / 10,
        $ifoSensor['ifoTempLowThldWarning']['value'] / 10,
        $ifoSensor['ifoTempHighThldWarning']['value'] / 10,
        $ifoSensor['ifoTempHighThldAlarm']['value'] / 10,
        $ifoSensor['ifoTempValue']['value'] / 10
    );

    $index++;
}

//echo "#### END discovery.temperature ifotec.inc.php #########################################################\n\n";