<?php
/**
 * websensor.inc.php
 *
 * LibreNMS pre-cache sensor discovery module for Comet System Web Sensor
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
$pre_cache['websensor_valuesInt'] = snmpwalk_group($device, 'valuesInt', 'T3610-MIB');
$pre_cache['websensor_settings'] = snmpwalk_group($device, 'settings', 'T3610-MIB');
