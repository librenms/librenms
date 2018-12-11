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
 * Notification Page
 * @author Daniel Preussker
 * @copyright 2015 Daniel Preussker, QuxLabs UG
 * @license GPL
 * @package LibreNMS
 * @subpackage Notifications
 */

use LibreNMS\Authentication\LegacyAuth;

header('Content-type: application/json');

$status    = 'error';
$message   = 'unknown error';
if (isset($_REQUEST['notification_id']) && isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'read' && dbInsert(array('notifications_id'=>$_REQUEST['notification_id'],'user_id'=>LegacyAuth::id(),'key'=>'read','value'=>1), 'notifications_attribs')) {
        $status  = 'ok';
        $message = 'Set as Read';
    } elseif ((!LegacyAuth::user()->hasGlobalAdmin() || LegacyAuth::user()->isDemoUser()) && $_REQUEST['action'] == 'stick' && dbInsert(array('notifications_id'=>$_REQUEST['notification_id'],'user_id'=>LegacyAuth::id(),'key'=>'sticky','value'=>1), 'notifications_attribs')) {
        $status  = 'ok';
        $message = 'Set as Sticky';
    } elseif ((!LegacyAuth::user()->hasGlobalAdmin() || LegacyAuth::user()->isDemoUser()) && $_REQUEST['action'] == 'unstick' && dbDelete('notifications_attribs', "notifications_id = ? && user_id = ? AND `key`='sticky'", array($_REQUEST['notification_id'],LegacyAuth::id()))) {
        $status  = 'ok';
        $message = 'Removed Sticky';
    }
} elseif ($_REQUEST['action'] == 'create' && (!LegacyAuth::user()->hasGlobalAdmin() || LegacyAuth::user()->isDemoUser()) && (isset($_REQUEST['title']) && isset($_REQUEST['body']))) {
    if (dbInsert(array('title'=>$_REQUEST['title'],'body'=>$_REQUEST['body'],'checksum'=>hash('sha512', LegacyAuth::id().'.LOCAL.'.$_REQUEST['title']),'source'=>LegacyAuth::id()), 'notifications')) {
        $status  = 'ok';
        $message = 'Created';
    }
} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'read-all-notif') {
    $unread = dbFetchColumn("SELECT `notifications_id` FROM `notifications` AS N WHERE NOT EXISTS ( SELECT 1 FROM `notifications_attribs` WHERE `notifications_id` = N.`notifications_id` AND `user_id`=? AND `key`='read' AND `value`=1)", array(LegacyAuth::id()));
    foreach ($unread as $notification_id) {
        dbInsert(
            array(
                'notifications_id' => $notification_id,
                'user_id' => LegacyAuth::id(),
                'key' => 'read',
                'value' => 1
            ),
            'notifications_attribs'
        );
    }
    $status = 'ok';
    $message = 'All notifications set as read';
} else {
    $status  = 'error';
    $message = 'ERROR: Missing Params';
}

die(json_encode(array(
    'status'       => $status,
    'message'      => $message,
)));
