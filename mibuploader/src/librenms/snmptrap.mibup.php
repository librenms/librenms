#!/usr/bin/env php
<?php
/**
 * snmptrap.mibup.php
 *
 * -Description-
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
 * @link       https://librenms.org
 * @copyright  2016 Florent Peterschmitt  
 * @author     Florent Peterschmitt <fpeterschmitt@capensis.fr>
 */


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_reporting', E_ALL);

require 'includes/defaults.inc.php';
require 'config.php';
require 'includes/definitions.inc.php';
require 'includes/functions.php';

$entry = explode(',', $argv[1], 3);

$sDevName = $entry['0'];

$device = @dbFetchRow('SELECT * FROM devices WHERE `hostname` = ?', array($sDevName));

if (!$device['device_id']) {
    $device = @dbFetchRow('SELECT * FROM ipv4_addresses AS A, ports AS I WHERE A.ipv4_address = ? AND I.port_id = A.port_id', array($sDevName));
}

if (!$device['device_id']) {
    $device = @dbFetchRow('SELECT * FROM devices WHERE `sysName` = ?', array($sDevName));
}

if (!$device['device_id']) {
    logfile('Device with name ' . $sDevName . ' not found.');
    exit;
}

require 'includes/snmptrap/genericTrap.inc.php';
