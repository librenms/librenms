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

$status    = 'error';
$message   = 'unknown error';
if (isset($_REQUEST['notification_id']) && isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'read' && dbInsert(array('notifications_id'=>$_REQUEST['notification_id'],'user_id'=>$_SESSION['user_id'],'key'=>'read','value'=>1),'notifications_attribs')) {
        $status  = 'ok';
        $message = 'Set as Read';
    }
    elseif ($_SESSION['userlevel'] == 10 && $_REQUEST['action'] == 'stick' && dbInsert(array('notifications_id'=>$_REQUEST['notification_id'],'user_id'=>$_SESSION['user_id'],'key'=>'sticky','value'=>1),'notifications_attribs')) {
        $status  = 'ok';
        $message = 'Set as Sticky';
    }
    elseif ($_SESSION['userlevel'] == 10 && $_REQUEST['action'] == 'unstick' && dbUpdate(array('key'=>'sticky','value'=>0),'notifications_attribs','notifications_id = ? && user_id = ?',array($_REQUEST['notification_id'],$_SESSION['user_id']))) {
        $status  = 'ok';
        $message = 'Removed Sticky';
    }
}
elseif ($_REQUEST['action'] == 'create' && $_SESSION['userlevel'] == 10 && (isset($_REQUEST['title']) && isset($_REQUEST['body']))) {
    if (dbInsert(array('title'=>$_REQUEST['title'],'body'=>$_REQUEST['body'],'checksum'=>hash('sha512',$_SESSION['user_id'].'.LOCAL.'.$_REQUEST['title']),'source'=>$_SESSION['user_id']),'notifications')) {
        $status  = 'ok';
        $message = 'Created';
    }
}
else {
    $status  = 'error';
    $message = 'ERROR: Missing Params';
}

die(json_encode(array(
    'status'       => $status,
    'message'      => $message,
)));

