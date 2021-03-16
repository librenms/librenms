<?php

/**
 * reset-port-state.inc.php
 *
 * LibreNMS form for reseting port state
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
 * @copyright  2021 Adam Bishop
 * @author     Adam Bishop <adam@omega.org.uk>
 */

use App\Models\Device;

if (! Auth::user()->hasGlobalAdmin()) {
    $response = [
        'status'  => 'error',
        'message' => 'Need to be admin',
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_POST['device_id'])) {
    if (! is_numeric($_POST['device_id'])) {
        $status = 'error';
        $message = 'Invalid device id ' . $_POST['device_id'];
    } else {
        $device = Device::find($_POST['device_id']);

        log_event('Port state history reset by ' . Auth::user()->username, $device);

        try {
            foreach ($device->ports()->get() as $port) {
                $port->ifSpeed_prev = null;
                $port->ifHighSpeed_prev = null;
                $port->ifOperStatus_prev = null;
                $port->ifAdminStatus_prev = null;

                $port->save();
            }
            $status = 'ok';
            $message = 'Port state cleared successfully';
        } catch (Exception $e) {
            $status = 'error';
            $message = 'Clearing port state failed: $e';
        }
    }
} else {
    $status = 'Error';
    $message = 'Undefined POST keys received';
}

$output = [
    'status'  => $status,
    'message' => $message,
];

header('Content-type: application/json');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
