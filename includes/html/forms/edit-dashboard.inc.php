<?php
/* Copyright (C) 2015 Daniel Preussker, QuxLabs UG <preussker@quxlabs.com>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>. */

/**
 * Edit Dashboards
 * @author Daniel Preussker
 * @copyright 2015 Daniel Preussker, QuxLabs UG
 * @license GPL
 */
header('Content-type: application/json');

$status = 'error';
$message = 'unknown error';

$dashboard_id = (int) $_REQUEST['dashboard_id'];
$dashboard_name = \LibreNMS\Util\Clean::html($_REQUEST['dashboard_name'], []);
$access = $_REQUEST['access'];

if (isset($dashboard_id) && isset($dashboard_name) && isset($access)) {
    if (dbUpdate(['dashboard_name'=> $dashboard_name, 'access'=> $access], 'dashboards', '(user_id = ? || access = 2) && dashboard_id = ?', [Auth::id(), $dashboard_id]) >= 0) {
        $status = 'ok';
        $message = 'Dashboard ' . $dashboard_name . ' updated';
    } else {
        $message = 'ERROR: Could not update dashboard ' . $dashboard_name;
    }
} else {
    $message = 'ERROR: Not enough params';
}

$response = [
    'status'        => $status,
    'message'       => $message,
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
