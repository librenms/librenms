<?php
/**
 * powerlogic.inc.php
 *
 * LibreNMS pre-cache sensor discovery module for Schneider PowerLogic
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
echo 'loadCurrentTable ';
$pre_cache['powerlogic_loadCurrentTable'] = snmpwalk_cache_index($device, 'loadCurrentTable', [], 'PM8ECCMIB');

echo 'powerTable ';
$pre_cache['powerlogic_powerTable'] = snmpwalk_cache_index($device, 'powerTable', [], 'PM8ECCMIB');

echo 'voltageTable ';
$pre_cache['powerlogic_voltageTable'] = snmpwalk_cache_index($device, 'voltageTable', [], 'PM8ECCMIB');

echo 'frequencyTable ';
$pre_cache['powerlogic_frequencyTable'] = snmpwalk_cache_index($device, 'frequencyTable', [], 'PM8ECCMIB');
