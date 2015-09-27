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
 * Notification Poller
 * @author Daniel Preussker
 * @copyright 2015 Daniel Preussker, QuxLabs UG
 * @license GPL
 * @package LibreNMS
 * @subpackage Notifications
 */

require_once 'includes/defaults.inc.php';
require_once 'config.php';
require_once $config['install_dir'].'/includes/definitions.inc.php';
require_once $config['install_dir'].'/includes/functions.php';

/**
 * Pull notifications from remotes
 * @return array Notifications
 */
function get_notifications() {
    global $config;
    $obj = array();
    foreach ($config['notifications'] as $name=>$url) {
        echo '[ '.date('r').' ] '.$url.' ';
        $feed = json_decode(json_encode(simplexml_load_string(file_get_contents($url))),true);
        if (isset($feed['channel'])) {
            $feed = parse_rss($feed);
        } else {
            $feed = parse_atom($feed);
        }
        array_walk($feed,function(&$items,$key,$url) { $items['source'] = $url; },$url);
        $obj = array_reverse(array_merge($obj,$feed));
        echo '('.sizeof($obj).')'.PHP_EOL;
    }
    return $obj;
}

/**
 * Post notifications to users
 * @return null
 */
function post_notifications() {
    $notifs = get_notifications();
    echo '[ '.date('r').' ] Updating DB ';
    foreach ($notifs as $notif) {
        if (dbFetchCell('select 1 from notifications where checksum = ?',array($notif['checksum'])) != 1 && dbInsert('notifications',$notif) > 0) {
            echo '.';
        }
    }
    echo ' Done';
    echo PHP_EOL;
}

/**
 * Parse RSS
 * @param array $feed RSS Object
 * @return array Parsed Object
 */
function parse_rss($feed) {
    $obj = array();
    if( !array_key_exists('0',$feed['channel']['item']) ) {
        $feed['channel']['item'] = array( $feed['channel']['item'] );
    }
    foreach ($feed['channel']['item'] as $item) {
        $obj[] = array('title'=>$item['title'],'body'=>$item['description'],'checksum'=>hash('sha512',$item['title'].$item['description']));
    }
    return $obj;
}

/**
 * Parse Atom
 * @param array $feed Atom Object
 * @return array Parsed Object
 */
function parse_atom($feed) {
    $obj = array();
    if( !array_key_exists('0',$feed['entry']) ) {
        $feed['entry'] = array( $feed['entry'] );
    }
    foreach ($feed['entry'] as $item) {
        $obj[] = array('title'=>$item['title'],'body'=>$item['content'],'checksum'=>hash('sha512',$item['title'].$item['content']));
    }
    return $obj;
}

post_notifications();
