<?php
/**
 * alert-contacts.inc.php
 *
 * LibreNMS alert-contactsinc.php for processor
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
$transport_config    = mres($_POST['transport-config']);

// If the transport config is not a default/none config, set the value to other
if ($transport_config != 'default' && $transport_config != 'none') {
    // Should probably search for the transport id and set it here
    $transport_config = 'other';
}

if (empty($name)) {
    $status = 'error';
    $message = 'No contact name provided';
} elseif (empty($transport_type) || empty($transport_config)) {
    $status = 'error';
    $message = 'Missing transport information';
} else {
    // Insert into alert-contacts
    if ($transport_config == 'other') {
        // Insert the transport id associated with contact
        $contact_id = dbInsert(array(
            'contact_name' => $name,
            'transport_type' => $transport_type,
            'transport_config' => $transport_config,
            'transport_id' => $transport_id
        ), 'alert_contacts');
    } else {
        // If no transport mapping, keep the value to be NULL
        $contact_id = dbInsert(array(
            'contact_name' => $name,
            'transport_type' => $transport_type,
            'transport_config' => $transport_config
        ), 'alert_contacts');
    }

    if ($contact_id) {
        // Grab config values
        if ($transport_type == 'email') {
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
        $config_type = 'contact';

        if ($contact_config) {
            foreach ($contact_config as $name => $value) {
                dbInsert(array(
                    'contact_or_transport_id' => $contact_id,
                    'config_type' => $config_type,
                    'config_name' => $name,
                    'config_value' => $value
                ), 'alert_configs');
            }
            
            $status = 'ok';
            $message = 'Updated alert contacts';
        }
    } else {
        $status = 'error';
        $message = 'Failed to add alert contact';
    }
}

die(json_encode([
    'status'       => $status,
    'message'      => $message
]));
