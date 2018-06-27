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

$transport_id        = $vars['transport_id'];
$name                = $vars['name'];
$is_default          = $vars['is_default'];
$transport_type      = $vars['transport-type'];

if ($is_default == 'on') {
    $is_default = true;
} else {
    $is_default = false;
}

if (empty($name)) {
    $status = 'error';
    $message = 'No transport name provided';
} elseif (empty($transport_type)) {
    $status = 'error';
    $message = 'Missing transport information';
} else {
    $details = array(
        'transport_name' => $name,
        'is_default' => $is_default
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
        $class = 'LibreNMS\\Alert\\Transport\\'.ucfirst($transport_type);

        if (!method_exists($class, 'configBuilder')) {
            die(json_encode([
                'status' => 'error',
                'message' => 'This transport type is not yet supported'
            ]));
        }
        
        // Build config values
        $result = call_user_func_array($class.'::configBuilder', array($vars));
        $transport_config = $result['transport_config'];
        $status = $result['status'];
        $message = $result['message'];

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
