<?php
/**
 * airflow.inc.php
 *
 * LibreNMS airflow module for discovery
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

echo 'Airflow: ';

// Include all discovery modules
$include_dir = 'includes/discovery/sensors/airflow';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['airflow']);

check_valid_sensors($device, 'airflow', $valid['sensor']);

echo "\n";
