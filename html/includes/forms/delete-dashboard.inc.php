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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Delete Dashboards
 * @author Daniel Preussker
 * @copyright 2015 Daniel Preussker, QuxLabs UG
 * @license GPL
 * @package LibreNMS
 * @subpackage Dashboards
 */
header('Content-type: application/json');

$status    = 'error';
$message   = 'unknown error';
if (isset($_REQUEST['dashboard_id'])) {
    dbDelete('users_widgets', 'user_id = ? && dashboard_id = ?', array($_SESSION['user_id'],$_REQUEST['dashboard_id']));
    if (dbDelete('dashboards', 'user_id = ? && dashboard_id = ?', array($_SESSION['user_id'],$_REQUEST['dashboard_id']))) {
        $status  = 'ok';
        $message = 'Deleted dashboard';
    } else {
        $message = 'ERROR: Could not delete dashboard '.$_REQUEST['dashboard_id'];
    }
} else {
    $message = 'ERROR: Not enough params';
}

die(json_encode(array(
    'status'       => $status,
    'message'      => $message,
)));
