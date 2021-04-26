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
 * Notification Poller
 * @copyright 2015 Daniel Preussker, QuxLabs UG
 * @copyright 2017 Tony Murray
 * @author    Daniel Preussker
 * @author    Tony Murray <murraytony@gmail.com>
 * @license   GPL
 * @link      https://www.librenms.org
 */

/**
 * Pull notifications from remotes
 * @return array Notifications
 */
function get_notifications()
{
    $obj = [];
    foreach (\LibreNMS\Config::get('notifications') as $name => $url) {
        echo '[ ' . date('r') . ' ] ' . $url . ' ';
        $feed = json_decode(json_encode(simplexml_load_string(file_get_contents($url))), true);
        if (isset($feed['channel'])) {
            $feed = parse_rss($feed);
        } else {
            $feed = parse_atom($feed);
        }
        array_walk($feed, function (&$items, $key, $url) {
            $items['source'] = $url;
        }, $url);
        $obj = array_merge($obj, $feed);
        echo '(' . sizeof($obj) . ')' . PHP_EOL;
    }
    $obj = array_sort_by_column($obj, 'datetime');

    return $obj;
}

/**
 * Post notifications to users
 * @return null
 */
function post_notifications()
{
    $notifs = get_notifications();
    echo '[ ' . date('r') . ' ] Updating DB ';
    foreach ($notifs as $notif) {
        if (dbFetchCell('select 1 from notifications where checksum = ?', [$notif['checksum']]) != 1 && dbInsert($notif, 'notifications') > 0) {
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
function parse_rss($feed)
{
    $obj = [];
    if (! array_key_exists('0', $feed['channel']['item'])) {
        $feed['channel']['item'] = [$feed['channel']['item']];
    }
    foreach ($feed['channel']['item'] as $item) {
        $obj[] = [
            'title'=>$item['title'],
            'body'=>$item['description'],
            'checksum'=>hash('sha512', $item['title'] . $item['description']),
            'datetime'=>strftime('%F', strtotime($item['pubDate']) ?: time()),
        ];
    }

    return $obj;
}

/**
 * Parse Atom
 * @param array $feed Atom Object
 * @return array Parsed Object
 */
function parse_atom($feed)
{
    $obj = [];
    if (! array_key_exists('0', $feed['entry'])) {
        $feed['entry'] = [$feed['entry']];
    }
    foreach ($feed['entry'] as $item) {
        $obj[] = [
            'title'=>$item['title'],
            'body'=>$item['content'],
            'checksum'=>hash('sha512', $item['title'] . $item['content']),
            'datetime'=>strftime('%F', strtotime($item['updated']) ?: time()),
        ];
    }

    return $obj;
}

/**
 * Create a new custom notification. Duplicate title+message notifications will not be created.
 *
 * @param string $title
 * @param string $message
 * @param int $severity 0=ok, 1=warning, 2=critical
 * @param string $source A string describing what created this notification
 * @param string $date
 * @return bool
 */
function new_notification($title, $message, $severity = 0, $source = 'adhoc', $date = null)
{
    $notif = [
        'title' => $title,
        'body' => $message,
        'severity' => $severity,
        'source' => $source,
        'checksum' => hash('sha512', $title . $message),
        'datetime' => strftime('%F', is_null($date) ? time() : strtotime($date)),
    ];

    if (dbFetchCell('SELECT 1 FROM `notifications` WHERE `checksum` = ?', [$notif['checksum']]) != 1) {
        return dbInsert($notif, 'notifications') > 0;
    }

    return false;
}

/**
 * Removes all notifications with the given title.
 * This should be used with care.
 *
 * @param string $title
 */
function remove_notification($title)
{
    $ids = dbFetchColumn('SELECT `notifications_id` FROM `notifications` WHERE `title`=?', [$title]);
    foreach ($ids as $id) {
        dbDelete('notifications', '`notifications_id`=?', [$id]);
        dbDelete('notifications_attribs', '`notifications_id`=?', [$id]);
    }
}
