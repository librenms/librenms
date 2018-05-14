<?php
/**
 * alert-contacts.inc.php
 *
 * LibreNMS alert-contacts.inc.php for processor
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

$contact_id          = mres($_POST['contact_id']);
$name                = mres($_POST['name']);
$transport_type      = mres($_POST['transport-type']);

if (empty($name)) {
    $status = 'error';
    $message = 'No contact name provided';
} elseif (empty($transport_type)) {
    $status = 'error';
    $message = 'Missing transport information';
} else {
    $details = array(
        'contact_name' => $name,
        'transport_type' => $transport_type
    );

    if (is_numeric($contact_id) && $contact_id > 0) {
        // Check if there have been changes to the transport type
        $sql  = 'SELECT `transport_type` FROM `alert_contacts` WHERE `contact_id`=? LIMIT 1';
        $type = dbFetchCell($sql, [$contact_id]);
        if ($type != $transport_type) {
            // Change to the transport type and therefore configuration fields
            $transportChange = true;
        }

        // Update the fields
        dbUpdate($details, 'alert_contacts', 'contact_id=?', [$contact_id]);
        $update = true;
    } else {
        // Insert the new alert contact
        $contact_id = dbInsert($details, 'alert_contacts');
    }

    if ($contact_id) {
        // Grab config values
        if ($transport_type == 'mail') {
            if ($_POST['email']) {
                $contact_config = array(
                    'email' => $_POST['email']
                );
            } else {
                $status = 'error';
                $message = 'Missing email information';
            }
        } elseif ($transport_type == 'ciscospark') {
            if ($_POST['api-token'] && $_POST['room-id']) {
                $contact_config = array(
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

        //Insert into alert-configs
        if ($contact_config) {
            // We will want to insert new values into the alert_config db if there has
            // been a transport type change
            if ($transportChange) {
                $update = false;
                $where = 'contact_id=?';
                dbDelete('alert_configs', $where, [$contact_id]);
            }

            foreach ($contact_config as $name => $value) {
                $detail = array(
                    'config_value' => $value
                );

                if ($update) {
                    //Update the values
                    $where = 'contact_id=?  and config_name=?';
                    $params = array($contact_id, $name);
                    dbUpdate($detail, 'alert_configs', $where, $params);
                } else {
                    //Insert the values
                    $detail = array_merge($detail, array(
                        'contact_id' => $contact_id,
                        'config_name' => $name
                    ));
                    dbInsert($detail, 'alert_configs');
                }
            }
            
            $status = 'ok';
            $message = 'Updated alert contacts';
        } else {
            // If no config info provided, we will have to delete the entry in the alert_contacts tbl
            $where = '`contact_id`=?';
            dbDelete('alert_contacts', $where, [$contact_id]);
        }
    } else {
        $status = 'error';
        $message = 'Failed to update contact';
    }
}

die(json_encode([
    'status'       => $status,
    'message'      => $message
]));
