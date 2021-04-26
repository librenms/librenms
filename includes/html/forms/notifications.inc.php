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
 * Notification Page
 * @author Daniel Preussker
 * @copyright 2015 Daniel Preussker, QuxLabs UG
 * @license GPL
 */
header('Content-type: application/json');

if (! isset($_REQUEST['action'])) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'ERROR: Missing Params',
    ]));
}

if (in_array($_REQUEST['action'], ['stick', 'unstick', 'create']) && ! Auth::user()->hasGlobalAdmin()) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'ERROR: Need to be GlobalAdmin or DemoUser',
    ]));
}

if ($_REQUEST['action'] == 'read' && isset($_REQUEST['notification_id'])) {
    if (dbInsert(['notifications_id'=>$_REQUEST['notification_id'], 'user_id'=>Auth::id(), 'key'=>'read', 'value'=>1], 'notifications_attribs')) {
        exit(json_encode([
            'status' => 'ok',
            'message' => 'Set as Read',
        ]));
    }
} elseif ($_REQUEST['action'] == 'read-all-notif') {
    $unread = dbFetchColumn("SELECT `notifications_id` FROM `notifications` AS N WHERE NOT EXISTS ( SELECT 1 FROM `notifications_attribs` WHERE `notifications_id` = N.`notifications_id` AND `user_id`=? AND `key`='read' AND `value`=1)", [Auth::id()]);
    foreach ($unread as $notification_id) {
        dbInsert(
            [
                'notifications_id' => $notification_id,
                'user_id' => Auth::id(),
                'key' => 'read',
                'value' => 1,
            ],
            'notifications_attribs'
        );
    }
    exit(json_encode([
        'status' => 'ok',
        'message' => 'All notifications set as read',
    ]));
} elseif ($_REQUEST['action'] == 'stick' && isset($_REQUEST['notification_id'])) {
    if (dbInsert(['notifications_id'=>$_REQUEST['notification_id'], 'user_id'=>Auth::id(), 'key'=>'sticky', 'value'=>1], 'notifications_attribs')) {
        exit(json_encode([
            'status' => 'ok',
            'message' => 'Set as Sticky',
        ]));
    }
} elseif ($_REQUEST['action'] == 'unstick' && isset($_REQUEST['notification_id'])) {
    if (dbDelete('notifications_attribs', "notifications_id = ? && user_id = ? AND `key`='sticky'", [$_REQUEST['notification_id'], Auth::id()])) {
        exit(json_encode([
            'status' => 'ok',
            'message' => 'Removed Sticky',
        ]));
    }
} elseif ($_REQUEST['action'] == 'create' && (! empty($_REQUEST['title']) && ! empty($_REQUEST['body']))) {
    if (dbInsert(['title'=>$_REQUEST['title'], 'body'=>$_REQUEST['body'], 'checksum'=>hash('sha512', Auth::id() . '.LOCAL.' . $_REQUEST['title']), 'source'=>Auth::id()], 'notifications')) {
        exit(json_encode([
            'status' => 'ok',
            'message' => 'Created',
        ]));
    }
} else {
    exit(json_encode([
        'status' => 'error',
        'message' => 'ERROR: Missing Params',
    ]));
}

exit(json_encode([
    'status'       => 'error',
    'message'      => 'unknown error',
]));
