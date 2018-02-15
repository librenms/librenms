<?php
/**
 * genericTrap.inc.php
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

// $entry[0] => device name (as string)
// $entry[1] => OID
// $entry[2] => param:value string

///*
$sFile = dirname(__FILE__) . '/../../html/plugins/MIBUploader/system/MIBUpAutoload.php';
require_once $sFile;
MIBUpAutoload::register();
//*/

try {
    $oCtrl = MIBUpCtrl::load('Trap');
    $oCtrl->trap($device['device_id'], $entry[1], $entry[2]);
} catch (MIBUpException $ex) {
    $sErr = 'MIBUploader trap receive failure: ' . $ex->getMessage();
    logfile($sErr);
    log_event($sErr);
}

/*
logfile("-------------");
logfile("Entries: " . MIBUpUtils::vardump($entry));
*/
