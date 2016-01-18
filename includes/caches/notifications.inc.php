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
 * Notification Cache
 * @author Daniel Preussker
 * @copyright 2015 Daniel Preussker, QuxLabs UG
 * @license GPL
 * @package LibreNMS
 * @subpackage Notifications
 */

$data['count']  = array(
    'query'  => 'select count(notifications.notifications_id) from notifications where not exists( select 1 from notifications_attribs where notifications.notifications_id = notifications_attribs.notifications_id and notifications_attribs.user_id = ?)',
    'params' => array( $_SESSION['user_id'] )
);

$data['unread'] = array(
    'query'  => 'select notifications.* from notifications where not exists( select 1 from notifications_attribs where notifications.notifications_id = notifications_attribs.notifications_id and notifications_attribs.user_id = ?) order by notifications.notifications_id desc',
    'params' => array( $_SESSION['user_id'] )
);

$data['sticky'] = array(
    'query'  => 'select notifications.*,notifications_attribs.user_id from notifications inner join notifications_attribs on notifications.notifications_id = notifications_attribs.notifications_id where notifications_attribs.key = "sticky" && notifications_attribs.value = 1 order by notifications_attribs.attrib_id desc',
);

$data['sticky_count'] = array(
    'query'  => 'select count(notifications.notifications_id) from notifications inner join notifications_attribs on notifications.notifications_id = notifications_attribs.notifications_id where notifications_attribs.key = "sticky" && notifications_attribs.value = 1',
);

$data['read'] = array(
    'query'  => 'select notifications.* from notifications inner join notifications_attribs on notifications.notifications_id = notifications_attribs.notifications_id where notifications_attribs.user_id = ? && ( notifications_attribs.key = "read" && notifications_attribs.value = 1) && not exists( select 1 from notifications_attribs where notifications.notifications_id = notifications_attribs.notifications_id and notifications_attribs.key = "sticky" && notifications_attribs.value = "1") order by notifications_attribs.attrib_id desc',
    'params' => array( $_SESSION['user_id'] )
);

