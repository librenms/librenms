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
 * Store per-widget settings
 * @author Daniel Preussker
 * @copyright 2015 Daniel Preussker, QuxLabs UG
 * @license GPL
 * @package LibreNMS
 * @subpackage Widgets
 */
header('Content-type: application/json');

$status    = 'error';
$message   = 'unknown error';
$widget_id = (int) $_REQUEST['id'];

if ($widget_id < 1) {
    $status  = 'error';
    $message = 'ERROR: malformed widget ID.';
}
else {
    $widget_settings = $_REQUEST['settings'];
    if (!is_array($widget_settings)) {
        $widget_settings = array();
    }
    if (dbFetchCell('select 1 from users_widgets inner join dashboards on users_widgets.dashboard_id = dashboards.dashboard_id where user_widget_id = ? && (users_widgets.user_id = ? || dashboards.access = 2)',array($widget_id,$_SESSION['user_id'])) == 1) {
        if (dbUpdate(array('settings'=>json_encode($widget_settings)),'users_widgets','user_widget_id=?',array($widget_id)) >= 0) {
            $status  = 'ok';
            $message = 'Updated';
        }
        else {
            $status  = 'error';
            $message = 'ERROR: Could not update';
        }
    }
    else {
        $status  = 'error';
        $message = 'ERROR: You have no write-access to this dashboard';
    }
}

die(json_encode(array(
    'status'  => $status,
    'message' => $message
)));
