<?php
/* Copyright (C) 2020 Thomas Berberich <sourcehhdoctor@gmail.com>
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
 * Copy Dashboards
 * @author Thoma Berberich
 * @copyright 2020 Thomas Berberich
 * @license GPL
 * @package LibreNMS
 * @subpackage Dashboards
 */

header('Content-type: application/json');

if (!Auth::check()) {
    $response = array(
        'status'  => 'error',
        'message' => 'Unauthenticated',
    );
    echo _json_encode($response);
    exit;
}

$status    = 'error';
$message   = 'unknown error';

$target_user_id = trim($_REQUEST['user_id']);
$dashboard_id = trim($_REQUEST['dashboard_id']);

$dashboard = dbFetchRow("SELECT * FROM `dashboards` WHERE `dashboard_id` = ? AND user_id = ?", array($dashboard_id, Auth::id()));

$dash_id = 0;
if ((! empty($dashboard)) && (! empty($target_user_id))) {
    $data = ['user_id' => $target_user_id,
             'dashboard_name' => $dashboard['dashboard_name'].'_'.Auth::user()->username
    ];
    $dash_id = dbInsert($data, 'dashboards');
}

if ($dash_id) {
    $widgets = dbFetchRows("SELECT * FROM `users_widgets` WHERE `dashboard_id` = ? AND user_id = ?", array($dashboard_id, Auth::id()));
    foreach ($widgets as $widget) {
        $data = ['user_id' => $target_user_id,
                 'widget_id' => $widget['widget_id'],
                 'col' => $widget['col'],
                 'row' => $widget['row'],
                 'size_x' => $widget['size_x'],
                 'size_y' => $widget['size_y'],
                 'title' => $widget['title'],
                 'refresh' => $widget['refresh'],
                 'settings' => $widget['settings'],
                 'dashboard_id' => $dash_id,
        ];
        dbInsert($data, 'users_widgets');
    }
}

if ($dash_id) {
    $status  = 'ok';
    $message = 'Dashboard copied';
} else {
    $status  = 'error';
    $message = 'ERROR: Could not copy Dashboard';
}

$response = array(
    'status'        => $status,
    'message'       => $message
);

echo _json_encode($response);
