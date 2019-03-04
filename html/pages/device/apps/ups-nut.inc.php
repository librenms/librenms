<?php
/*
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
* @subpackage webui
* @link       http://librenms.org
* @copyright  2019 LibreNMS
* @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

global $config;

$graphs = [
    'ups-nut_remaining' => 'Remaining time: ',
    'ups-nut_load' => 'Load: ',
    'ups-nut_voltage_battery' => 'Battery voltage: ',
    'ups-nut_charge' => 'Charge: ',
    'ups-nut_voltage_input' => 'Input voltage: ',
];

include "app.bootstrap.inc.php";
