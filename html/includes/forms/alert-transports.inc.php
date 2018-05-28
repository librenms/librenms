<?php
/**
 * alert-transports.inc.php
 *
 * LibreNMS alert-transports.inc.php for processor
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
 * @copyright  2018 Vivia Nguyen-Tran
 * @author     Vivia Nguyen-Tran <vivia@ualberta.ca>
 */

use LibreNMS\Authentication\Auth;

header('Content-type: application/json');

if (!Auth::user()->hasGlobalAdmin()) {
    die(json_encode([
        'status' => 'error',
        'message' => 'ERROR: You need to be admin'
    ]));
}

$status = 'ok';
$message = '';

$transport_id        = mres($_POST['transport_id']);
$name                = mres($_POST['name']);
$transport_type      = mres($_POST['transport-type']);

if (empty($name)) {
    $status = 'error';
    $message = 'No transport name provided';
} elseif (empty($transport_type)) {
    $status = 'error';
    $message = 'Missing transport information';
} else {
    $details = array(
        'transport_name' => $name
    );

    if (is_numeric($transport_id) && $transport_id > 0) {
        // Update the fields -- json config field will be updated later
        dbUpdate($details, 'alert_transports', 'transport_id=?', [$transport_id]);
    } else {
        // Insert the new alert transport
        $newEntry = true;
        $transport_id = dbInsert($details, 'alert_transports');
    }

    if ($transport_id) {
        // Grab config values
        if ($transport_type == 'mail') {
            if ($_POST['email']) {
                $transport_config = array(
                    'email' => $_POST['email']
                );
            } else {
                $status = 'error';
                $message = 'Missing email information';
            }
        } elseif ($transport_type == 'ciscospark') {
            if ($_POST['api-token'] && $_POST['room-id']) {
                $transport_config = array(
                    'api-token' => $_POST['api-token'],
                    'room-id' => $_POST['room-id']
                );
            } else {
                $status = 'error';
                $message = 'Missing API token or Room ID';
            }
        } else {
            $status = 'error';
            $message = 'No transport type provided';
        }

        //Update the json config field
        if ($transport_config) {
            $transport_config = json_encode($transport_config);
            $detail = array(
                'transport_type'   => $transport_type,
                'transport_config' => $transport_config
            );
            $where = 'transport_id=?';

            dbUpdate($detail, 'alert_transports', $where, [$transport_id]);
            
            $status = 'ok';
            $message = 'Updated alert transports';
        } else {
            if ($newEntry) {
                //If no config info provided, we will have to delete the new entry in the alert_transports tbl
                $where = '`transport_id`=?';
                dbDelete('alert_transports', $where, [$transport_id]);
            }
        }
    } else {
        $status = 'error';
        $message = 'Failed to update transport';
    }
}

die(json_encode([
    'status'       => $status,
    'message'      => $message
]));
